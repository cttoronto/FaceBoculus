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
            echo '<div class="pic"><a href="#" onclick="poke(\'' . $value["id"] . '\')">';
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
      (function() {
        var e = document.createElement('script'); e.async = true;
        e.src = document.location.protocol +
          '//connect.facebook.net/en_US/all.js';
        document.getElementById('fb-root').appendChild(e);
      }());


      function login() {
        FB.login(function(response) {
         // handle the response
         grabFriends();
        }, {scope: 'basic_info,user_photos,friends_photos,user_status,friends_status,read_friendlists,publish_actions,publish_stream'});
      }

      function grabFriends() {
        FB.api('/me/friends', { fields: 'id, name', limit: 3 },  function(response) 
        {
           if (response.error) 
           {
               alert(JSON.stringify(response.error));
           } 
           else 
           {    
               // alert("Loading friends...");
               console.log("fdata: " + response.data);
               response.data.forEach(function(item) 
               {           
                  document.getElementById('friends').innerHTML+='<image src="https://graph.facebook.com/'+item['id']+'/picture?type=large&return_ssl_results=1" />'; 
                  document.getElementById('friends').innerHTML+='<br />'+item['name']+'';          
               });
           }


    });

      

        
      }

      function poke(friendId) {
          var opts = {
                message : 'You\'ve been poked by me on FaceBoculus',
                name : '',
                link : 'http://www.paper-face.com/cttoronto/faceboculus',
                description : 'FaceBoculus',
                picture : 'http://paper-face.com/cttoronto/faceboculus/poke.png'
            };

            FB.api('/' + friendId + '/feed', 'post', opts, function(response)
            {
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

      <?php if ($user) { ?>
          grabFriends();
        <? } ?>
    </script>
  </body>
</html>
