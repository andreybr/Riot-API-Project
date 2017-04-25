<html lang="en-US" ng-app="myApp" ng-controller="myCtrl">
  <head>
    <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.6.4/angular.min.js"></script>
  </head>
  <body>

    <?php
      $summonerName = "RustyShack1ef0rd";
      $apiKey = "8dc5f64f-e827-4905-b157-8b2799d455e2";
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_HEADER, 0);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      
      curl_setopt($ch, CURLOPT_URL, "https://na1.api.riotgames.com/lol/summoner/v3/summoners/by-name/" . $summonerName . "?api_key=8dc5f64f-e827-4905-b157-8b2799d455e2");
      $output = curl_exec($ch);
      $summonerJSON = $output;
      $summoner = json_decode($output, TRUE);
       
      curl_setopt($ch, CURLOPT_URL, "https://na.api.riotgames.com/api/lol/NA/v1.3/stats/by-summoner/" . $summoner["id"] . "/ranked?season=SEASON2017&api_key=8dc5f64f-e827-4905-b157-8b2799d455e2"); 
      $output = curl_exec($ch);
      $statsJSON = $output;
      $stats = json_decode($output, TRUE);
      
      curl_setopt($ch, CURLOPT_URL, "https://na.api.riotgames.com/api/lol/NA/v2.5/league/by-summoner/" . $summoner["id"] . "?api_key=8dc5f64f-e827-4905-b157-8b2799d455e2");
      $output = curl_exec($ch);
      $leagueJSON = $output;
      $league = json_decode($output, TRUE);
      
      curl_setopt($ch, CURLOPT_URL, "https://na1.api.riotgames.com/lol/match/v3/matchlists/by-account/209787471?api_key=" . $apiKey);
      $output = curl_exec($ch);
      $matchListJSON = $output;
      
      // get indepth summoner info from league api
      $leagueExtra;   
      for ($i = 0; $i < count($league[$summoner["id"]][0]["entries"]); $i++) {
        if($league[$summoner["id"]][0]["entries"][$i]["playerOrTeamName"] == $summonerName) {
          $leagueExtra = $league[$summoner["id"]][0]["entries"][$i];
          break;
        }
      }
      $leagueExtraJSON = json_encode($leagueExtra, TRUE);
    
      // sort ranked champions by most played
      // starts at 1 to skip total stats, which has invalid champ id
      $champions = array() ;
      for ($x = 0; $x < count($stats["champions"]); ++$x) {
        $values = array();
        
        curl_setopt($ch, CURLOPT_URL, "https://na1.api.riotgames.com/lol/static-data/v3/champions/" . $stats["champions"][$x]["id"] . "?locale=en_US&champData=all&api_key=8dc5f64f-e827-4905-b157-8b2799d455e2");
        $output = curl_exec($ch);
        $champStats = json_decode($output, TRUE);

        if(empty($champStats["name"])) {
          continue;
        }
        $values['name'] = $champStats["name"];
        $values['id'] = $stats["champions"][$x]["id"];
        $values['totalSessionsPlayed'] = $stats["champions"][$x]["stats"]["totalSessionsPlayed"];
        $values['totalSessionsWon'] = $stats["champions"][$x]["stats"]["totalSessionsWon"];
        $values['totalSessionsLost'] = $stats["champions"][$x]["stats"]["totalSessionsLost"];
        array_push($champions, $values);
      }
      $championsJSON = json_encode($champions, TRUE);
      
      curl_close($ch);
    ?>  
    <style type="text/css">
    #page {
      width: 70%;
      margin: auto;
    }
    /* header ========================================= */
    .header {
      padding: 5px;
      background: #49c19b;
      color: #ffffff;
    }
    .header-left {
      display: inline-block;
    }
    .header-right {
      display: inline-block;
      vertical-align: top;
    }
    .header-name {
      font-size: 23px;
      font-weight: bold;
      color: #ffffff;
      margin: 5px;
    }
    .header-level {
      margin: 5px;
    }
    .header-info {
      margin: 5px;
      vertical-align: center;
    }  
    .header-league-image {
      margin: 5px;
      width: 30px;
      height: 30px;
    }
    /* header ========================================= */   
    .sort-bar {
      text-align: center;
    }
    .sort-bar a {
      display: inline-block;
      text-decoration: none;
      padding: 5px;
    }    
    .box {
      background:#272727;
      color: white;
      margin: 0px 0px;
      padding: 3px;
    }
    .box:hover {
      background: #424242;
    }
    .box img{
      border-radius: 0px;
    } 
    .expand-button {
      font-family: Arial, sans-serif;
      text-align: center;
      background: transparent;
      padding: 5px;
      cursor: pointer;
    }
    </style>
    <script>
    var summoner = <?php echo $summonerJSON ?>;
    var league = <?php echo $leagueJSON ?>;
    var leagueExtra = <?php echo $leagueExtraJSON ?>;

    
    var app = angular.module('myApp', []);
    app.controller('myCtrl', function($scope) {
        $scope.summonerName = summoner['name'];
        $scope.summonerLevel = summoner['summonerLevel'];
        $scope.summonerTier = league[summoner['id']][0]["tier"];
        $scope.summonerDivision = leagueExtra["division"];
        $scope.champions = <?php echo $championsJSON ?>;
        $scope.sort = 'name';
        $scope.listLimit = 5;
        $scope.toggleState = 'Expand';
        $scope.matchList = <?php echo $matchListJSON ?>;
        console.log($scope.matchList["totalGames"]);
        
        $scope.roleList;
        // count roles played
        //for(x = 0; x < matchList.length; x++) {
          //matchList["matches"][x]["lane"] 
          //roleList[matchList["matches"][x]["lane"]][matchList["matches"][x]["role"]]++; 
        //}
        //sort champions by criteria
        $scope.championSort = function(sortType) {
          $scope.sort = sortType;
        }
        //format champion name so it can be used to lookup an image
        $scope.formatName = function(name) {
          name = name.replace(/ /g,'');
          name = name.replace(/'/g,'');
          return name;
        }
        //hide/expand champion list
        $scope.toggleList = function() {
          if($scope.listLimit != 5) {
            $scope.listLimit = 5;
            $scope.toggleState = 'Expand';
          }
          else {
            $scope.listLimit = 20;
            $scope.toggleState = 'Collapse';
          }
        }
    });
    </script>
    <div id="page">
      <div class="header">
        <div class="header-left">
          <img ng-src="http://avatar.leagueoflegends.com/na/'{{ summonerName }}'.png">
        </div>
        <div class="header-right">
          <div class='header-name'>{{summonerName}}</div>
          <div class='header-level'>{{"Level " + summonerLevel}}</div>
          <div class='header-info'><img class='header-league-image' ng-src="/web2/images/tier-icons/{{summonerTier}}_{{summonerDivision}}"/> {{summonerTier + " " + summonerDivision}}</div>
        </div>
      </div>
      <div class="sort-bar">
        <a href="" ng-click="championSort('totalSessionsPlayed')">Most Played</a>|
        <a href="" ng-click="championSort('totalSessionsWon')">Most Wins</a>|
        <a href="" ng-click="championSort('name')">Name</a>
        <a href="" ng-click="championSort('id')">ID</a>
      </div>
      <div class="most-played">
        <div class="box" ng-repeat="champion in champions | orderBy: sort :true | limitTo: listLimit" ng-if="champion['name'] != null">
          <img ng-src="http://opgg-static.akamaized.net/images/lol/champion/{{ formatName(champion['name']) }}.png?image=c_scale,w_45">
          {{ champion['name'] + " " + champion['id'] + "   " + champion['totalSessionsWon'] + "-" + champion['totalSessionsLost']}}
        </div>
        <div class="expand-button" ng-click="toggleList()" ng-bind="toggleState"></div>
      </div>
    </div>
  </body>
</html>
