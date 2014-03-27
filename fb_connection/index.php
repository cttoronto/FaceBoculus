<?php

require 'src/facebook.php';

$facebook = new Facebook(array(
  'appId'  => '220762168118305',
  'secret' => 'cc93d71c061ff715d8a6a3ec62ca7147',
));

// See if there is a user from a cookie
$user = $facebook->getUser();

if ($user) {
  try {
    // Proceed knowing you have a logged in user who's authenticated.
    $user_profile = $facebook->api('/me');
    // $friends = $facebook->api('me/friends?fields=id,name&limit=5000');
    $friends = $facebook->api('/me/friends');

        echo '<ul>';
        foreach ($friends["data"] as $value) {
          if (strpos($value["name"], "'") === false) {
            echo '<li>';
            echo '<div class="pic"><a href="#" onclick="PostOnFriendsWall(\'' . $value["id"] . '\', \'' . $value["name"] . '\')">';
            echo '<img src="https://graph.facebook.com/' . $value["id"] . '/picture"/>';
            echo '</a></div>';
            echo '<div class="picName">'.$value["name"].'</div>'; 
            echo '</li>';
          }
        }
        echo '</ul>';

  } catch (FacebookApiException $e) {
    echo '<pre>'.htmlspecialchars(print_r($e, true)).'</pre>';
    $user = null;
  }
}

?>
<!DOCTYPE html>
<html xmlns:fb="http://www.facebook.com/2008/fbml">
  <head>
    <script src="//ajax.googleapis.com/ajax/libs/jquery/2.0.0/jquery.min.js"></script>

  </head>
  <body>

    <?php if ($user) { ?>
      Your user profile is
      <pre>
        <?php print htmlspecialchars(print_r($user_profile, true)) ?>
      </pre>
    <?php } else { ?>
      <!-- <fb:login-button></fb:login-button> -->
      <div id="login"><a onclick="login(); return false;">Login</a></div>
    <?php } ?>
    <div id="fb-root"></div>
    <script>
  

      window.fbAsyncInit = function() {
        FB.init({
          appId: '<?php echo $facebook->getAppID() ?>',
          cookie: true,
          xfbml: true,
          oauth: true
        });
        FB.Event.subscribe('auth.login', function(response) {
          window.location.reload();

        });
        FB.Event.subscribe('auth.logout', function(response) {
          window.location.reload();
        });
      };
      
      var token = {};

      function login() {
        FB.login(function(response) {
          console.log(response);
          localStorage.setItem("token", response.authResponse.accessToken);
         // handle the response
         // token = response.authResponse.accessToken;
         // grabFriends();
        }, {scope: 'user_status,friends_status,publish_actions'});
      }


       function logResponse(response) {
        console.log(response);
        if (response && !response.error) {
          /* handle the result */
          console.log("success");
        } else {
          console.log(response.error);
        }
      }

       function PostOnFriendsWall (friendId, friendName) {

        // var access_token=document.getElementById("access_token").value;
        // var sendername=document.getElementById("sendername").value;
        // status1 = document.getElementById('message').value;
        // var facebookid = document.getElementsByName("facebookid");
        var token    = localStorage.getItem("token"),
            postData = {
                "message": "I am FaceBoculus-ing and would like to poke @" + friendName + "!!!!",
                // "access_token": token,
                // "from": { "id": "<?=$user_profile["id"];?>", "name": "<?=$user_profile["name"];?>" },
                // "to": { "name": friendName, "id": friendId },
                // "message_tags": [{ "name": friendName, "id": friendId, "offset": 46, "length": friendName.length }],
                "tags": friendId,
                "picture": "http://paper-face.com/cttoronto/faceboculus/poke.jpg",
                "caption": "POKE",
                "description": "brought to you by FaceBoculus"
                // "app_id": "<?php echo $facebook->getAppID() ?>"
            };

        console.log(token, token.id, postData);

        FB.api(
            // "/" + friendId + "/feed",
            "/me/feed/",
            "POST",
            postData,   // object
            logResponse // Callback function created just above this method
        );
        
        // FB.api('/' + friendId + '/feed', 'POST', publish, function(response) {alert("posted");});
      } 


     (function() {
        var e = document.createElement('script'); e.async = true;
        e.src = document.location.protocol +
          '//connect.facebook.net/en_US/all.js';
        document.getElementById('fb-root').appendChild(e);
      }());
    </script>
  </body>
</html>
