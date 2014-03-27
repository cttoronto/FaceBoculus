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
            echo '<li>';
            echo '<div class="pic"><a href="#" onclick="PostOnFriendsWall(\'' . $value["id"] . '\')">';
            echo '<img src="https://graph.facebook.com/' . $value["id"] . '/picture"/>';
            echo '</a></div>';
            echo '<div class="picName">'.$value["name"].'</div>'; 
            echo '</li>';
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
      
      var token = "";

      function login() {
        FB.login(function(response) {
         // handle the response
         token = response.authResponse.accessToken;
         // grabFriends();
        }, {scope: 'user_status,friends_status,publish_actions'});
      }

      

        
      

      function poke(friendId) {
          var opts = {
                message : 'You\'ve been poked by me on FaceBoculus',
                name : '',
                link : 'http://www.paper-face.com/cttoronto/faceboculus',
                description : 'FaceBoculus',
                picture : 'http://paper-face.com/cttoronto/faceboculus/poke.jpg'
            };

            FB.api('/' + friendId + '/feed', 'post', opts, function(response)
            {
              console.log(response);
                if (!response || response.error)
                {
                    alert('Posting error occured');
                }
                else
                {
                    alert('Success - Post ID: ' + response.id);
                }
            });
      }


      function PostOnFriendsWall(friendId)
      {


        // var access_token=document.getElementById("access_token").value;
        // var sendername=document.getElementById("sendername").value;
        // status1 = document.getElementById('message').value;
        // var facebookid = document.getElementsByName("facebookid");
        FB.api(
          "/" + friendId + "/feed",
          "POST",
          {
              
                  "message": "You have been poked!!!!",
                  "from": token.id,
                  // "to": friendId,
                  "picture": "http://paper-face.com/cttoronto/faceboculus/poke.jpg",
                  "caption": "POKE",
                  "description": "brought to you by FaceBoculus"
              
          },
          function (response) {
             console.log(response);
            if (response && !response.error) {
              /* handle the result */
                 console.log("success");
            }
          }
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
