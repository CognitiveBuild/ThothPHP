<?php

echo <<<EOT
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE plist PUBLIC "-//Apple//DTD PLIST 1.0//EN" "http://www.apple.com/DTDs/PropertyList-1.0.dtd">
<plist version="1.0">
<dict>
    <key>items</key>
    <array>
        <dict>
            <key>assets</key>
            <array>
                <dict>
                    <key>kind</key>
                    <string>software-package</string>
                    <key>url</key>
                    <string>https://thoth-assets.mybluemix.net/api/v1/app/{$build->getId()}</string>
                </dict>
            </array>
            <key>metadata</key>
            <dict>
                <key>bundle-identifier</key>
                <string>{$build->getUid()}</string>
                <key>bundle-version</key>
                <string>{$build->getVersion()}</string>
                <key>kind</key>
                <string>software</string>
                <key>title</key>
                <string>{$build->getDisplay()}</string>
            </dict>
        </dict>
    </array>
</dict>
</plist>
EOT;
