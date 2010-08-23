<?php
header('Content-Type: text/javascript');

echo "var i18n = new Hash();\n";
echo "i18n.set('auth_failed', '"._('Authentication failed, please double-check your password and try again')."');\n";
echo "i18n.set('in_maintenance', '"._('The system is in maintenance mode, please contact your administrator for more information')."');\n";
echo "i18n.set('internal_error', '"._('An internal error occured, please contact your administrator')."');\n";
echo "i18n.set('invalid_user', '"._('You specified an invalid login, please double-check and try again')."');\n";
echo "i18n.set('service_not_available', '"._('The service is not available, please contact your administrator for more information')."');\n";
echo "i18n.set('unauthorized_session_mode', '"._('You are not authorized to launch a session in this mode')."');\n";
echo "i18n.set('user_with_active_session', '"._('You already have an active session')."');\n";

echo "i18n.set('session_close_unexpected', '"._('Server: session closed unexpectedly')."');\n";
echo "i18n.set('session_end_ok', '"._('Your session has ended, you can now close the window')."');\n";
echo "i18n.set('session_end_unexpected', '"._('Your session has ended unexpectedly')."');\n";
echo "i18n.set('error_details', '"._('error details')."');\n";
echo "i18n.set('close_this_window', '"._('Close this window')."');\n";
echo "i18n.set('start_another_session', '"._('Click <a href="javascript:;" onclick="hideEnd(); showLogin(); return false;">here</a> to start a new session')."');\n";
echo "i18n.set('start_another_session_popup', '"._('Click <a href="index.php">here</a> to start a new session')."');\n";

echo "i18n.set('suspend', '"._('suspend')."');\n";
echo "i18n.set('resume', '"._('resume')."');\n";
