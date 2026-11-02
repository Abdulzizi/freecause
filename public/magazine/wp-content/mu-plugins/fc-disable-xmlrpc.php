<?php
/**
 * Plugin Name: FC Disable XML-RPC
 * Description: Refuses all XML-RPC requests with a 403. nginx config is not in this repo, so this is the in-repo block.
 */

if (defined('XMLRPC_REQUEST') && XMLRPC_REQUEST) {
    status_header(403);
    exit;
}
