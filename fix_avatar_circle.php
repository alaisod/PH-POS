<?php
$path = __DIR__ . '/assets/css/style.css';
$content = file_get_contents($path);

$replacements = [
    // Fix 1: register .customer-badge .avatar - add overflow:hidden
    [
        'old' => ".register .register-right .customer-badge .avatar {\r\n  float: left;\r\n  display: inline-block;\r\n  width: 60px;\r\n  height: 60px;\r\n}",
        'new' => ".register .register-right .customer-badge .avatar {\r\n  float: left;\r\n  display: inline-block;\r\n  width: 60px;\r\n  height: 60px;\r\n  overflow: hidden;\r\n  flex-shrink: 0;\r\n}"
    ],
    // Fix 2: register .customer-badge .avatar img - add more robust sizing
    [
        'old' => ".register .register-right .customer-badge .avatar img {\r\n  width: 100%;\r\n  height: 100%;\r\n  object-fit: cover;\r\n  border-radius: 50%;\r\n}",
        'new' => ".register .register-right .customer-badge .avatar img {\r\n  width: 100% !important;\r\n  height: 100% !important;\r\n  min-width: 100%;\r\n  min-height: 100%;\r\n  max-width: 100%;\r\n  max-height: 100%;\r\n  object-fit: cover;\r\n  border-radius: 50%;\r\n}"
    ],
    // Fix 3: generic .customer-badge .avatar - add overflow:hidden
    [
        'old' => ".customer-badge .avatar {\r\n  float: left;\r\n  display: inline-block;\r\n  width: 60px;\r\n  height: 60px;\r\n}",
        'new' => ".customer-badge .avatar {\r\n  float: left;\r\n  display: inline-block;\r\n  width: 60px;\r\n  height: 60px;\r\n  overflow: hidden;\r\n  flex-shrink: 0;\r\n}"
    ],
    // Fix 4: generic .customer-badge .avatar img - add more robust sizing
    [
        'old' => ".customer-badge .avatar img {\r\n  width: 100%;\r\n  height: 100%;\r\n  object-fit: cover;\r\n  border-radius: 50%;\r\n}",
        'new' => ".customer-badge .avatar img {\r\n  width: 100% !important;\r\n  height: 100% !important;\r\n  min-width: 100%;\r\n  min-height: 100%;\r\n  max-width: 100%;\r\n  max-height: 100%;\r\n  object-fit: cover;\r\n  border-radius: 50%;\r\n}"
    ],
    // Fix 5: register .customer-badge.suggestions .avatar - add overflow:hidden
    [
        'old' => ".register .register-right .customer-badge.suggestions .avatar {\r\n  width: 35px;\r\n  height: 35px;\r\n}",
        'new' => ".register .register-right .customer-badge.suggestions .avatar {\r\n  width: 35px;\r\n  height: 35px;\r\n  overflow: hidden;\r\n  flex-shrink: 0;\r\n}"
    ],
    // Fix 6: register .customer-badge.suggestions .avatar img - more robust
    [
        'old' => ".register .register-right .customer-badge.suggestions .avatar img {\r\n  width: 100%;\r\n  height: 100%;\r\n  object-fit: cover;\r\n  border-radius: 50%;\r\n}",
        'new' => ".register .register-right .customer-badge.suggestions .avatar img {\r\n  width: 100% !important;\r\n  height: 100% !important;\r\n  min-width: 100%;\r\n  min-height: 100%;\r\n  max-width: 100%;\r\n  max-height: 100%;\r\n  object-fit: cover;\r\n  border-radius: 50%;\r\n}"
    ],
    // Fix 7: generic .customer-badge.suggestions .avatar
    [
        'old' => ".customer-badge.suggestions .avatar {\r\n  width: 35px !important;\r\n  height: 35px !important;\r\n}",
        'new' => ".customer-badge.suggestions .avatar {\r\n  width: 35px !important;\r\n  height: 35px !important;\r\n  overflow: hidden !important;\r\n  flex-shrink: 0;\r\n}"
    ],
    // Fix 8: generic .customer-badge.suggestions .avatar img
    [
        'old' => ".customer-badge.suggestions .avatar img {\r\n  width: 100%;\r\n  height: 100%;\r\n  object-fit: cover;\r\n  border-radius: 50%;\r\n}",
        'new' => ".customer-badge.suggestions .avatar img {\r\n  width: 100% !important;\r\n  height: 100% !important;\r\n  min-width: 100%;\r\n  min-height: 100%;\r\n  max-width: 100%;\r\n  max-height: 100%;\r\n  object-fit: cover;\r\n  border-radius: 50%;\r\n}"
    ],
];

$count = 0;
foreach ($replacements as $rep) {
    $c = 0;
    $content = str_replace($rep['old'], $rep['new'], $content, $c);
    if ($c > 0) {
        echo "Replacement \"" . substr($rep['old'], 0, 50) . "...\" => " . $c . " time(s)\n";
        $count += $c;
    } else {
        echo "NOT FOUND: \"" . substr($rep['old'], 0, 50) . "...\"\n";
    }
}

file_put_contents($path, $content);
echo "\nTotal $count replacement(s) applied.\n";
