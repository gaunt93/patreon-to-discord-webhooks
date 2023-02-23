<?PHP
//
// Inspired by: https://github.com/Stonebound/patreon-to-discord-webhooks
//
// URL FROM DISCORD WEBHOOK SETUP
$webhook = "YOURDISCORDWEBHOOKHERE"; 
$secret_webhook_id = "YOURESECRETWEBHOOKHERE";
// this saves the post data you get on your endpoint
$data = @file_get_contents('php://input');
// decode json post data to arrays
$event_data = json_decode($data, true);

// also get the headers patreon sends
$X_Patreon_Event     = $_SERVER['HTTP_X_PATREON_EVENT'];
$X_Patreon_Signature = $_SERVER['HTTP_X_PATREON_SIGNATURE'];

// verify signature
$signature = hash_hmac('md5', $data, $secret_webhook_id);
if (!hash_equals($X_Patreon_Signature, $signature)) {
    die("Patreon Signature didn't match, got: " . $X_Patreon_Signature . " expected: " . $signature);
}

// get all the user info
$pledge_amount = $event_data['data']['attributes']['pledge_amount_cents'];
$patron_id     = $event_data['data']['relationships']['user']['data']['id'];
$campaign_id   = $event_data['data']['relationships']['campaign']['data']['id'];

foreach ($event_data['included'] as $included_data) {
    if ($included_data['type'] == 'user' && $included_data['id'] == $patron_id) {
        $user_data = $included_data;
    }
    if ($included_data['type'] == 'campaign' && $included_data['id'] == $campaign_id) {
        $campaign_data = $included_data;
    }
}

$patron_url = $user_data['attributes']['url'];
$patron_fullname = $user_data['attributes']['full_name'];
$patreon_discord = $user_data['attributes']['discord_id'];

$campaign_sum    = $campaign_data['attributes']['pledge_sum'];
$patron_count    = $campaign_data['attributes']['patron_count'];

// send event to discord
if ($X_Patreon_Event == "members:create") {
    $discordmessage = ":star: <@" . $patreon_discord . ">" . " just pledged for $" . number_format(($pledge_amount /100), 2, '.', ' ') . "! - New total: $" . number_format(($campaign_sum / 100), 2, '.', ' ') . " by " . $patron_count . " patreons";
} else if ($X_Patreon_Event == "members:delete") {
    $discordmessage = ":disappointed: <@" . $patreon_discord . ">" . " just removed their pledge! - New total: $" . number_format(($campaign_sum / 100), 2, '.', ' ') . " by " . $patron_count . " patreons";
} else if ($X_Patreon_Event == "members:update") {
    $discordmessage = ":open_mouth: <@" . $patreon_discord . ">" . " just updated their pledge to $" . number_format(($pledge_amount /100), 2, '.', ' ') . "! - New total: $" . number_format(($campaign_sum / 100), 2, '.', ' ') . " by " . $patron_count . " patreons";
} else {
    $discordmessage = $X_Patreon_Event . ": something happened with Patreon ¯\_(ツ)_/¯";
}
// members:create	        Triggered when a new member is created. Note that you may get more than one of these per patron if they delete and renew their membership. Member creation only occurs if there was no prior payment between patron and creator.
// members:update	        Triggered when the membership information is changed. Includes updates on payment charging events
// members:delete	        Triggered when a membership is deleted. Note that you may get more than one of these per patron if they delete and renew their membership. Deletion only occurs if no prior payment happened, otherwise pledge deletion is an update to member status.
// members:pledge:create	Triggered when a new pledge is created for a member. This includes when a member is created through pledging, and when a follower becomes a patron.
// members:pledge:update	Triggered when a member updates their pledge.
// members:pledge:delete	Triggered when a member deletes their pledge.
// posts:publish	        Triggered when a post is published on a campaign.
// posts:update	            Triggered when a post is updated on a campaign.
// posts:delete	            Triggered when a post is deleted on a campaign.

    function discordmsg($msg, $webhook) {
        if($webhook != "") {
            $ch = curl_init( $webhook );
            curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
            curl_setopt( $ch, CURLOPT_POST, 1);
            curl_setopt( $ch, CURLOPT_POSTFIELDS, $msg);
            curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt( $ch, CURLOPT_HEADER, 0);
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);
 
            $response = curl_exec( $ch );
            // If you need to debug, or find out why you can't send message uncomment line below, and execute script.
            echo $response;
            curl_close( $ch );
        }
    }

    $timestamp = date("c", strtotime("now"));
    $msg = json_encode([
    // Message
    "content" =>  $discordmessage,
 
    // Username
    "username" => " Discord-linked Patreon Bot",
 
    // text-to-speech
    "tts" => false
	
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
 
    discordmsg($msg, $webhook); // SENDS MESSAGE TO DISCORD
    echo "sent?";
?>
