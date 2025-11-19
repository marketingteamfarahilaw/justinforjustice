<?php
/**
 * Plugin Name: IP Spam Blocker & Suspect Lister
 * Description: Detects possible spammers by simple heuristics (failed logins, suspicious comments, rapid requests) and allows admins to view, block and unblock IP addresses.
 * Version: 1.1
 * Author: Raimehn Roger
 * License: FLF
 *
 * Quick tuning (change these values as needed):
 * - SPB_MAX_FAILED_LOGINS: number of failed logins before marking suspicious
 * - SPB_COMMENT_MIN_LENGTH: minimum acceptable comment length (shorter than this flagged)
 * - SPB_MAX_COMMENTS_PER_MINUTE: how many comments from same IP per minute before flagged
 * - SPB_SUSPECT_TTL: how long (seconds) a suspect record is kept without further activity
 */

if (!defined('ABSPATH')) exit;

define('SPB_OPTION_BLOCKED', 'spb_blocked_ips');
define('SPB_OPTION_SUSPECTS', 'spb_suspect_ips');
define('SPB_OPTION_LOG', 'spb_event_log');

if (!defined('SPB_MAX_FAILED_LOGINS')) define('SPB_MAX_FAILED_LOGINS', 5);
if (!defined('SPB_COMMENT_MIN_LENGTH')) define('SPB_COMMENT_MIN_LENGTH', 15);
if (!defined('SPB_MAX_COMMENTS_PER_MINUTE')) define('SPB_MAX_COMMENTS_PER_MINUTE', 5);
if (!defined('SPB_SUSPECT_TTL')) define('SPB_SUSPECT_TTL', 60 * 60 * 24 * 7); // 7 days
if (!defined('SPB_EVENT_LOG_LIMIT')) define('SPB_EVENT_LOG_LIMIT', 200);

// ======= Helpers ========
function spb_get_remote_ip() {
    if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) return sanitize_text_field($_SERVER['HTTP_CF_CONNECTING_IP']); // Cloudflare
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        return sanitize_text_field(trim($ips[0]));
    }
    return isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field($_SERVER['REMOTE_ADDR']) : '0.0.0.0';
}

function spb_get_blocked() {
    $b = get_option(SPB_OPTION_BLOCKED, array());
    if (!is_array($b)) $b = array();
    return $b;
}
function spb_set_blocked($arr) { update_option(SPB_OPTION_BLOCKED, $arr); }

function spb_get_suspects() {
    $s = get_option(SPB_OPTION_SUSPECTS, array());
    if (!is_array($s)) $s = array();
    return $s;
}
function spb_set_suspects($arr) { update_option(SPB_OPTION_SUSPECTS, $arr); }

function spb_log_event($event) {
    $log = get_option(SPB_OPTION_LOG, array());
    if (!is_array($log)) $log = array();
    array_unshift($log, array(
        'time' => current_time('mysql'),
        'event' => $event
    ));
    if (count($log) > SPB_EVENT_LOG_LIMIT) $log = array_slice($log, 0, SPB_EVENT_LOG_LIMIT);
    update_option(SPB_OPTION_LOG, $log);
}

function spb_prune_suspects() {
    $suspects = spb_get_suspects();
    $now = time();
    foreach ($suspects as $ip => $data) {
        if (isset($data['last_seen']) && ($now - intval($data['last_seen'])) > SPB_SUSPECT_TTL) {
            unset($suspects[$ip]);
        }
    }
    spb_set_suspects($suspects);
}

// ======= Blocking enforcement ========
add_action('init', 'spb_block_enforce', 1);
function spb_block_enforce() {
    $blocked = spb_get_blocked();
    $ip = spb_get_remote_ip();
    if (isset($blocked[$ip])) {
        status_header(403);
        wp_die(__('Access from your IP address has been blocked for suspicious activity.'), __('Blocked'), array('response' => 403));
    }
}

// ======= Detection: failed logins ========
add_action('wp_login_failed', 'spb_failed_login_tracker', 10, 1);
function spb_failed_login_tracker($username) {
    $ip = spb_get_remote_ip();
    $suspects = spb_get_suspects();
    if (!isset($suspects[$ip])) {
        $suspects[$ip] = array(
            'failed_logins' => 0,
            'comments' => 0,
            'last_seen' => time(),
            'blocked' => false,
        );
    }
    $suspects[$ip]['failed_logins'] = intval($suspects[$ip]['failed_logins']) + 1;
    $suspects[$ip]['last_seen'] = time();

    spb_set_suspects($suspects);
    spb_log_event("Failed login #{$suspects[$ip]['failed_logins']} from IP {$ip} (user: {$username})");

    if ($suspects[$ip]['failed_logins'] >= SPB_MAX_FAILED_LOGINS) {
        spb_mark_block($ip, "Exceeded failed login threshold ({$suspects[$ip]['failed_logins']})");
    }
}

// ======= Detection: comments ========
add_action('comment_post', 'spb_comment_tracker', 10, 3);
function spb_comment_tracker($comment_id, $approved, $commentdata) {
    $ip = spb_get_remote_ip();
    $content = isset($commentdata['comment_content']) ? trim($commentdata['comment_content']) : '';
    $suspects = spb_get_suspects();
    if (!isset($suspects[$ip])) {
        $suspects[$ip] = array(
            'failed_logins' => 0,
            'comments' => 0,
            'timestamps' => array(),
            'last_seen' => time(),
            'blocked' => false,
        );
    }
    $suspects[$ip]['comments'] = intval($suspects[$ip]['comments']) + 1;
    $suspects[$ip]['last_seen'] = time();

    if (!isset($suspects[$ip]['timestamps']) || !is_array($suspects[$ip]['timestamps'])) $suspects[$ip]['timestamps'] = array();
    $suspects[$ip]['timestamps'][] = time();
    if (count($suspects[$ip]['timestamps']) > 20) $suspects[$ip]['timestamps'] = array_slice($suspects[$ip]['timestamps'], -20);

    spb_set_suspects($suspects);

    if (strlen($content) < SPB_COMMENT_MIN_LENGTH) {
        spb_log_event("Short comment (" . strlen($content) . ") from $ip — comment #{$suspects[$ip]['comments']}");
        spb_mark_suspect($ip, "Short comment length (" . strlen($content) . ")");
    }

    $now = time();
    $count_recent = 0;
    foreach ($suspects[$ip]['timestamps'] as $t) {
        if ($now - $t <= 60) $count_recent++;
    }
    if ($count_recent > SPB_MAX_COMMENTS_PER_MINUTE) {
        spb_log_event("Rapid commenting ($count_recent/min) by $ip");
        spb_mark_block($ip, "Rapid commenting: {$count_recent}/minute");
    }
}

function spb_mark_suspect($ip, $reason = '') {
    $suspects = spb_get_suspects();
    if (!isset($suspects[$ip])) $suspects[$ip] = array();
    $suspects[$ip]['last_seen'] = time();
    if (!isset($suspects[$ip]['notes']) || !is_array($suspects[$ip]['notes'])) $suspects[$ip]['notes'] = array();
    $suspects[$ip]['notes'][] = array('time'=>current_time('mysql'), 'note'=>substr($reason,0,200));
    spb_set_suspects($suspects);
    spb_log_event("Marked suspect $ip — $reason");
}

function spb_mark_block($ip, $reason = '') {
    $blocked = spb_get_blocked();
    if (isset($blocked[$ip])) return;

    $blocked[$ip] = array(
        'time' => current_time('mysql'),
        'reason' => substr($reason,0,200),
    );
    spb_set_blocked($blocked);

    $suspects = spb_get_suspects();
    if (isset($suspects[$ip])) {
        $suspects[$ip]['blocked'] = true;
        $suspects[$ip]['block_reason'] = $reason;
        spb_set_suspects($suspects);
    }

    spb_log_event("Blocked IP $ip — $reason");
}

// ======= Admin UI ========
add_action('admin_menu', 'spb_admin_menu');
function spb_admin_menu() {
    add_options_page('IP Spam Blocker', 'IP Spam Blocker', 'manage_options', 'spb-ip-blocker', 'spb_admin_page');
    add_action('admin_init', 'spb_register_settings');
}

function spb_register_settings() {
    register_setting('spb_options_group', SPB_OPTION_BLOCKED);
    register_setting('spb_options_group', SPB_OPTION_SUSPECTS);
    register_setting('spb_options_group', SPB_OPTION_LOG);
}

function spb_admin_page() {
    if (!current_user_can('manage_options')) wp_die('Unauthorized');

    // Handle actions (including manual block with optional reason)
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && check_admin_referer('spb_admin_action', 'spb_admin_nonce')) {
        if (!empty($_POST['action_type'])) {
            $action = sanitize_text_field($_POST['action_type']);
            $ip = !empty($_POST['ip']) ? sanitize_text_field($_POST['ip']) : '';
            if ($action === 'block' && $ip) {
                $reason = isset($_POST['reason']) ? sanitize_text_field($_POST['reason']) : '';
                if ($reason === '') $reason = 'Manually blocked by admin';
                spb_mark_block($ip, $reason);
                echo '<div class="updated"><p>Blocked IP ' . esc_html($ip) . ' — Reason: ' . esc_html($reason) . '</p></div>';
            } elseif ($action === 'unblock' && $ip) {
                $blocked = spb_get_blocked();
                if (isset($blocked[$ip])) {
                    unset($blocked[$ip]);
                    spb_set_blocked($blocked);
                    spb_log_event("Unblocked IP $ip (manual)");
                    echo '<div class="updated"><p>Unblocked IP ' . esc_html($ip) . '</p></div>';
                }
            } elseif ($action === 'clear_log') {
                update_option(SPB_OPTION_LOG, array());
                echo '<div class="updated"><p>Event log cleared</p></div>';
            } elseif ($action === 'purge_suspects') {
                update_option(SPB_OPTION_SUSPECTS, array());
                echo '<div class="updated"><p>Suspect list cleared</p></div>';
            }
        }
    }

    spb_prune_suspects();

    $blocked = spb_get_blocked();
    $suspects = spb_get_suspects();
    $log = get_option(SPB_OPTION_LOG, array());
    ?>
    <div class="wrap">
        <h1>IP Spam Blocker</h1>
        <p>Manage suspected and blocked IP addresses. Adjust thresholds by editing the plugin constants at the top of the file.</p>

        <!-- ===== Manually Add IP to Block (with optional Reason) ===== -->
        <h2>Manually Add IP to Block</h2>
        <form method="post" style="margin-bottom:20px;">
            <?php wp_nonce_field('spb_admin_action', 'spb_admin_nonce'); ?>
            <input type="hidden" name="action_type" value="block">
            <!-- IPv4 pattern (simple). Remove pattern to allow IPv6 as well. -->
            <input type="text" name="ip" placeholder="Enter IP address (e.g., 203.0.113.45)" required
                   pattern="^([0-9]{1,3}\.){3}[0-9]{1,3}$"
                   style="min-width:260px;margin-right:8px;">
            <input type="text" name="reason" placeholder="Reason / Email (optional)"
                   style="min-width:340px;margin-right:8px;">
            <button class="button button-primary" type="submit">Block IP</button>
        </form>

        <h2>Currently blocked IPs (<?php echo count($blocked); ?>)</h2>
        <table class="widefat">
            <thead><tr><th>IP</th><th>Blocked At</th><th>Reason / Email</th><th>Action</th></tr></thead>
            <tbody>
            <?php if (empty($blocked)): ?>
                <tr><td colspan="4">No blocked IPs</td></tr>
            <?php else: foreach ($blocked as $ip => $meta): ?>
                <tr>
                    <td><?php echo esc_html($ip); ?></td>
                    <td><?php echo esc_html($meta['time']); ?></td>
                    <td><?php echo esc_html(isset($meta['reason']) ? $meta['reason'] : ''); ?></td>
                    <td>
                        <form method="post" style="display:inline;">
                            <?php wp_nonce_field('spb_admin_action', 'spb_admin_nonce'); ?>
                            <input type="hidden" name="ip" value="<?php echo esc_attr($ip); ?>">
                            <input type="hidden" name="action_type" value="unblock">
                            <button class="button" type="submit">Unblock</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>

        <h2>Suspected IPs (<?php echo count($suspects); ?>)</h2>
        <p>These IPs have suspicious activity. You can block them manually.</p>
        <table class="widefat">
            <thead><tr><th>IP</th><th>Failed Logins</th><th>Comments</th><th>Last Seen</th><th>Notes</th><th>Action</th></tr></thead>
            <tbody>
            <?php if (empty($suspects)): ?>
                <tr><td colspan="6">No suspects tracked</td></tr>
            <?php else: foreach ($suspects as $ip => $meta): ?>
                <tr>
                    <td><?php echo esc_html($ip); ?></td>
                    <td><?php echo esc_html(isset($meta['failed_logins']) ? $meta['failed_logins'] : 0); ?></td>
                    <td><?php echo esc_html(isset($meta['comments']) ? $meta['comments'] : 0); ?></td>
                    <td><?php echo esc_html(isset($meta['last_seen']) ? date('Y-m-d H:i:s', intval($meta['last_seen'])) : ''); ?></td>
                    <td>
                        <?php
                        if (!empty($meta['notes']) && is_array($meta['notes'])) {
                            foreach (array_slice($meta['notes'], -3) as $n) {
                                echo '<div style="font-size:12px;margin-bottom:4px;">' . esc_html($n['time']) . ' — ' . esc_html($n['note']) . '</div>';
                            }
                        } else {
                            echo '-';
                        }
                        ?>
                    </td>
                    <td>
                        <form method="post" style="display:inline;">
                            <?php wp_nonce_field('spb_admin_action', 'spb_admin_nonce'); ?>
                            <input type="hidden" name="ip" value="<?php echo esc_attr($ip); ?>">
                            <input type="hidden" name="action_type" value="block">
                            <button class="button button-primary" type="submit">Block</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>

        <h2>Event Log (<?php echo count($log); ?>)</h2>
        <form method="post" style="margin-bottom:12px;">
            <?php wp_nonce_field('spb_admin_action', 'spb_admin_nonce'); ?>
            <input type="hidden" name="action_type" value="clear_log">
            <button class="button" type="submit">Clear Log</button>
        </form>
        <div style="max-height:300px; overflow:auto; background:#fff; border:1px solid #ddd; padding:8px;">
            <?php if (empty($log)): ?><div>No events yet.</div><?php else: foreach ($log as $entry): ?>
                <div style="font-size:13px;border-bottom:1px solid #eee;padding:6px 0;">
                    <strong><?php echo esc_html($entry['time']); ?></strong>: <?php echo esc_html($entry['event']); ?>
                </div>
            <?php endforeach; endif; ?>
        </div>

        <h2>Maintenance</h2>
        <form method="post" style="display:inline-block;margin-right:8px;">
            <?php wp_nonce_field('spb_admin_action', 'spb_admin_nonce'); ?>
            <input type="hidden" name="action_type" value="purge_suspects">
            <button class="button" type="submit">Clear suspect list</button>
        </form>
        <form method="post" action="" style="display:inline-block;">
            <?php wp_nonce_field('spb_admin_action', 'spb_admin_nonce'); ?>
            <input type="hidden" name="action_type" value="reindex">
            <button class="button" type="submit" disabled>Reindex (coming soon)</button>
        </form>

    </div>
    <?php
}

// ======= Uninstall cleanup ========
register_uninstall_hook(__FILE__, 'spb_uninstall_cleanup');
function spb_uninstall_cleanup() {
    delete_option(SPB_OPTION_BLOCKED);
    delete_option(SPB_OPTION_SUSPECTS);
    delete_option(SPB_OPTION_LOG);
}

register_activation_hook(__FILE__, 'spb_on_activate');
function spb_on_activate() { spb_log_event("Plugin activated"); }

// Optional admin_post endpoint (kept for completeness)
add_action('admin_post_spb_manual_action', 'spb_handle_admin_post');
function spb_handle_admin_post() {
    if (!current_user_can('manage_options')) wp_die('Unauthorized');
    check_admin_referer('spb_admin_action', 'spb_admin_nonce');

    $ip = isset($_POST['ip']) ? sanitize_text_field($_POST['ip']) : '';
    $action = isset($_POST['do']) ? sanitize_text_field($_POST['do']) : '';
    $reason = isset($_POST['reason']) ? sanitize_text_field($_POST['reason']) : '';

    if ($ip && $action) {
        if ($action === 'block') {
            if ($reason === '') $reason = 'Manually blocked by admin';
            spb_mark_block($ip, $reason);
        }
        if ($action === 'unblock') {
            $blocked = spb_get_blocked();
            if (isset($blocked[$ip])) {
                unset($blocked[$ip]);
                spb_set_blocked($blocked);
                spb_log_event("Unblocked IP $ip via admin_post");
            }
        }
    }
    wp_redirect(wp_get_referer() ? wp_get_referer() : admin_url('options-general.php?page=spb-ip-blocker'));
    exit;
}
