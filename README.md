# patreon-to-discord-webhooks

Updated for Patreon APIv2, and modern discord webhooks. This was an update from Stonebound, with modifications to ping discord users when they update their patreon.

## Setup

1. Download script
2. Put it somewhere accessible from the web; needs to be under a https://
3. create a Patreon webhook [here](https://www.patreon.com/portal/registration/register-webhooks) with the 3 triggers listed below and point them at the script location
    * `members:create`
    * `members:update`
    * `members:delete`
3. edit script and add your Discord webbhook URL and Patreon secret at the top
