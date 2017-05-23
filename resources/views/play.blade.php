<!DOCTYPE html>
<html lang="en">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/css/bootstrap.min.css" integrity="sha384-rwoIResjU2yc3z8GV/NPeZWAv56rSmLldC3R/AZzGRnGxQQKnKkoFVhFQhNUwEyJ" crossorigin="anonymous">
    <head>
      <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
          <meta name="description" content="">
            <meta name="author" content="">
              <link rel="icon" href="../../favicon.ico">
                <title>COMP484 Project 5</title>
        <script type='text/javascript' src='https://cdnjs.cloudflare.com/ajax/libs/socket.io/1.7.3/socket.io.js'></script>

        <script type="text/javascript">
          var socket = io('http://localhost:3000', {path: '/socket.io'}); // connect to server
          var playerNum = 0;
          var hand = [];
          var reDrawNum = 0;
          var reDrawArray = [];
          var roundNum = 0;
          var roundsAllowed = 2; //the number of rounds allowed 
          var score =0;
          var numOfPlayers = 0;

          //figure out from the user how many players will be playing, then tell the server 

          socket.emit('fromClient', {id:'foo'}); // send fromClient message to server

          function clearPlayerSelect(){
            var element = document.getElementById('numForm');
            element.innerHTML = '';
            element.parentNode.removeChild(element);

          }
          function determinePlayerNum(){
            document.getElementById("numForm").submit();
              console.log(document.getElementById("option0").checked + ',' + document.getElementById("option1").checked + ',' +
              document.getElementById("option2").checked);
            if(document.getElementById('option0').checked){
              numOfPlayers = 2;
            }
            if (document.getElementById('option1').checked){
              numOfPlayers = 3;
            } 
            if(document.getElementById('option2').checked){
              numOfPlayers = 4;
            }
            clearPlayerSelect();
            socket.emit('determinePlayerNum', {num:numOfPlayers});
          }

          function cardDraw() {
            reDrawNum = 0;
            if(roundNum < roundsAllowed){
              document.getElementById("cardForm").submit();

              console.log('Redraw cards!');

              console.log(document.getElementById("cardz0").checked + ',' + document.getElementById("cardz1").checked + ',' +
              document.getElementById("cardz2").checked + ',' + document.getElementById("cardz3").checked + ',' + 
              document.getElementById("cardz4").checked);

              if(document.getElementById("cardz0").checked){
                reDrawNum++;
                reDrawArray[0] = 1;
              }
              if(document.getElementById("cardz1").checked){
                reDrawNum++;
                reDrawArray[1] = 1;
              }
              if(document.getElementById("cardz2").checked){
                reDrawNum++;
                reDrawArray[2] = 1;
              }
              if(document.getElementById("cardz3").checked){
                reDrawNum++;
                reDrawArray[3] = 1;
              }
              if(document.getElementById("cardz4").checked){
                reDrawNum++;
                reDrawArray[4] = 1;
              
              }

              socket.emit('requestReDraw',{reDrawNum: reDrawNum, playerNum: playerNum });
              roundNum++;
              } 
          }

          function calculateScores(){
            document.getElementById('display').innerHTML = "Game Over";
              var suiteString="";
              var handString="";
              var scoreString=""; //used to give the server a nice display of the winning hand
              //strip out the suites/card value and make into two strings
              //for example handString = AAAQQ  is 3 Aces and 2 Queens
              //for example suiteString = CCCCC is all five cards are clubs
            for(var i = 0;i<5;i++){
              if(hand[i].charAt(0) == 1){
                //There is a 10 in the hand and it needs to be parsed differently
                  suiteString = suiteString + hand[i].charAt(2);
                  handString = handString + hand[i].charAt(0);
              } else {
                //pase the hand normally
                  suiteString = suiteString + hand[i].charAt(1);
                  handString = handString + hand[i].charAt(0);
              }
            }

            console.log(handString);
            console.log(suiteString);
            if(check3Pair(handString) ==2){

              //you have a full house
              console.log('full house');
              score = 20;
              scoreString = "Full House!";
            } else if(check2Pair(handString) == 1){
              //You have a single 2 pair
              console.log('single 2 pair');
              score = 15;
              scoreString = "Single 2 Pair!";
            } else if(check2Pair(handString) ==2){
              //you have 2 2 pairs
              console.log('2 2 pairs');
              score = 16;
              scoreString = "2 2 Pairs!";
            } else if(check3Pair(handString) ==1){
              //you have a 3 of a kind
              console.log('3 of a kind');
              score = 17;
              scoreString = "3 of a kind!";
            } else if(checkStraight(handString) == 1){
              //you have a regular straight

              console.log('regular straight');
              scoreString = "Regular Straight!";
              score = 18;
              if(checkFlush(suiteString)==1){
                //you have a straight flush
                console.log('straight flush');
                score = 22;
                scoreString = "Straight Flush!";
              }
            } else if(checkStraight(handString)==2){
              //you have a "royal" straight
              console.log('royal straight');
              if(checkFlush(suiteString)==1){
                //you have a royal flush
                console.log('royal flush');
                scoreString = "Royal Flush!";
                score = 23;
              }
            }else if(checkFlush(suiteString) ==1){
                //you have a flush

                score = 19;
                scoreString = "Flush!";
            }else if(checkFourOfAKind(handString)==1){
              //you have 4 of a kind
              console.log('4 of a kind');
              score = 21;
              scoreString = "4 of a kind!";
            } else {
              //rip 
              console.log('check highest card');
              score = highCard(handString);
              scoreString = "Highest Card!";
            }
            
            document.getElementById('submit').innerHTML = "You have a " + scoreString;
            //send the score value to the server
             socket.emit('scoreCalculated',{score: score, playerNum: playerNum, scoreString: scoreString});
          }   

 

          function highCard(handdata){
            var bool = 0;
            if( (handdata.match(/2/g) || []).length == 1){
              //high card 2
              bool = 2;
            }
            if( (handdata.match(/3/g) || []).length == 1){
              //high card 3
              bool = 3;
            }
            if( (handdata.match(/4/g) || []).length == 1){
              //high card 4
              bool = 4;
            }
            if( (handdata.match(/5/g) || []).length == 1){
              //high card 5
              bool = 5;
            }
            if( (handdata.match(/6/g) || []).length == 1){
              //high card 6
              bool = 6;
            }
            if( (handdata.match(/7/g) || []).length == 1){
              //high card 7
              bool = 7;
            }
            if( (handdata.match(/8/g) || []).length == 1){
              //high card 8
              bool = 8;
            }
            if( (handdata.match(/9/g) || []).length == 1){
              //high card 9
              bool = 9;
            }
            if( (handdata.match(/1/g) || []).length == 1){
              //high card 10
              bool = 10;
            } 
            if( (handdata.match(/J/g) || []).length == 1){
              //high card jack
              bool = 11;
            }
            if( (handdata.match(/Q/g) || []).length == 1){
              //high card queen
              bool = 12;
            }
            if( (handdata.match(/K/g) || []).length == 1){
              //high card king
              bool = 13;
            }
            if( (handdata.match(/A/g) || []).length == 1){
              //high card ace
              bool = 14;
            }
            return bool;
          }

          function check2Pair(handdata){  //runs 1 for a single 2 pair, and 2 for 2 2pairs
              var bool = 0;
              if((handdata.match(/A/g) || []).length == 2){
                //you have 2 aces
                var temp = handdata.replace(/A/gi, "");
                bool = check2Pair(temp) + 1;
              } else if((handdata.match(/K/g) || []).length == 2){
                  //you have 2 kings
                var temp = handdata.replace(/K/gi, "");
                bool = check2Pair(temp) + 1;
              } else if ((handdata.match(/Q/g) || []).length == 2){
                  //you have 2 Queens
                var temp = handdata.replace(/Q/gi, "");
                bool = check2Pair(temp) + 1;
              } else if ((handdata.match(/J/g) || []).length == 2){
                  //you have 2 jacks
                  var temp = handdata.replace(/J/gi, "");
                    bool = check2Pair(temp) + 1;
              } else if((handdata.match(/1/g) || []).length == 2){
                  //you have 2 10
                  var temp = handdata.replace(/1/gi, "");
                  bool = check2Pair(temp) + 1;
              } else if ((handdata.match(/9/g) || []).length == 2){
                  //you have 2 9
                  var temp = handdata.replace(/9/gi, "");
                  bool = check2Pair(temp) + 1;
              } else if ((handdata.match(/8/g) || []).length == 2){
                  //you have 2 8
                  var temp = handdata.replace(/8/gi, "");
                    bool = check2Pair(temp) + 1;
              } else if((handdata.match(/7/g) || []).length == 2){
                  //you have 2 7
                  var temp = handdata.replace(/7/gi, "");
                    bool = check2Pair(temp) + 1;
              } else if ((handdata.match(/6/g) || []).length == 2){
                  //you have 2 6
                  var temp = handdata.replace(/6/gi, "");
                    bool = check2Pair(temp) + 1;
              } else if ((handdata.match(/5/g) || []).length == 2){
                  //you have 2 5
                  var temp = handdata.replace(/5/gi, "");
                bool = check2Pair(temp) + 1;
              } else if((handdata.match(/4/g) || []).length == 2){
                  //you have 2 4
                  var temp = handdata.replace(/4/gi, "");
                bool = check2Pair(temp) + 1;
              } else if ((handdata.match(/3/g) || []).length == 2){
                  //you have 2 3
                  var temp = handdata.replace(/3/gi, "");
                bool = check2Pair(temp) + 1;
              } else if ((handdata.match(/2/g) || []).length == 2){
                  //you have 2 2
                  var temp = handdata.replace(/2/gi, "");
                bool = check2Pair(temp) + 1;
              } else {
                bool = 0;
              }

                
              return bool;
          }

          function check3Pair(handdata){ //returns 1 for a 3 pair, and 2 for a full house
              var bool = 0;
              if((handdata.match(/A/g) || []).length == 3){
                //you have 3 aces
                var temp = handdata.replace(/A/gi, "");
                bool = check2Pair(temp) + 1;
              } else if((handdata.match(/K/g) || []).length == 3){
                  //you have 3 kings
                var temp = handdata.replace(/K/gi, "");
                bool = check2Pair(temp) + 1;
              } else if ((handdata.match(/Q/g) || []).length == 3){
                  //you have 3 Queens
                var temp = handdata.replace(/Q/gi, "");
                bool = check2Pair(temp) + 1;
              } else if ((handdata.match(/J/g) || []).length == 3){
                  //you have 3 jacks
                  var temp = handdata.replace(/J/gi, "");
                    bool = check2Pair(temp) + 1;
              } else if((handdata.match(/1/g) || []).length == 3){
                  //you have 3 10
                  var temp = handdata.replace(/1/gi, "");
                  bool = check2Pair(temp) + 1;
              } else if ((handdata.match(/9/g) || []).length == 3){
                  //you have 3 9
                  var temp = handdata.replace(/9/gi, "");
                  bool = check2Pair(temp) + 1;
              } else if ((handdata.match(/8/g) || []).length == 3){
                  //you have 3 8
                  var temp = handdata.replace(/8/gi, "");
                    bool = check2Pair(temp) + 1;
              } else if((handdata.match(/7/g) || []).length == 3){
                  //you have 3 7
                  var temp = handdata.replace(/7/gi, "");
                    bool = check2Pair(temp) + 1;
              } else if ((handdata.match(/6/g) || []).length == 3){
                  //you have 3 6
                  var temp = handdata.replace(/6/gi, "");
                    bool = check2Pair(temp) + 1;
              } else if ((handdata.match(/5/g) || []).length == 3){
                  //you have 3 5
                  var temp = handdata.replace(/5/gi, "");
                bool = check2Pair(temp) + 1;
              } else if((handdata.match(/4/g) || []).length == 3){
                  //you have 3 4
                  var temp = handdata.replace(/4/gi, "");
                bool = check2Pair(temp) + 1;
              } else if ((handdata.match(/3/g) || []).length == 3){
                  //you have 3 3
                  var temp = handdata.replace(/3/gi, "");
                bool = check2Pair(temp) + 1;
              } else if ((handdata.match(/2/g) || []).length == 3){
                  //you have 3 2
                  var temp = handdata.replace(/2/gi, "");
                bool = check2Pair(temp) + 1;
              } else {
                bool = 0;
              }

                
              return bool;
          }

          function checkStraight(handdata){ //this function returns 1 for a normal straight, and returns 2 for a AKQJ10 royal straight
            var bool = 0;

            if( ((handdata.match(/A/g) || []).length == 1) && ((handdata.match(/K/g) || []).length == 1) && ((handdata.match(/Q/g) || []).length == 1) && ((handdata.match(/J/g) || []).length == 1) && ((handdata.match(/1/g) || []).length == 1)) {
              //you have a royal straight AKQJ10
              bool = 2;
            } else if( ((handdata.match(/K/g) || []).length == 1) && ((handdata.match(/Q/g) || []).length == 1) && ((handdata.match(/J/g) || []).length == 1) && ((handdata.match(/1/g) || []).length == 1) && ((handdata.match(/9/g) || []).length == 1)) {
              //you have a straight KQJ109
              bool = 1;
            } else if( ((handdata.match(/Q/g) || []).length == 1) && ((handdata.match(/J/g) || []).length == 1) && ((handdata.match(/1/g) || []).length == 1) && ((handdata.match(/9/g) || []).length == 1) && ((handdata.match(/8/g) || []).length == 1)) {
              //you have a straight QJ1098
              bool = 1;
            } else if( ((handdata.match(/J/g) || []).length == 1) && ((handdata.match(/1/g) || []).length == 1) && ((handdata.match(/9/g) || []).length == 1) && ((handdata.match(/8/g) || []).length == 1) && ((handdata.match(/7/g) || []).length == 1)) {
              //you have a straight J10987
              bool = 1;
            } else if( ((handdata.match(/1/g) || []).length == 1) && ((handdata.match(/9/g) || []).length == 1) && ((handdata.match(/8/g) || []).length == 1) && ((handdata.match(/7/g) || []).length == 1) && ((handdata.match(/6/g) || []).length == 1)) {
              //you have a straight 109876
              bool = 1;
            } else if( ((handdata.match(/9/g) || []).length == 1) && ((handdata.match(/8/g) || []).length == 1) && ((handdata.match(/7/g) || []).length == 1) && ((handdata.match(/6/g) || []).length == 1) && ((handdata.match(/5/g) || []).length == 1)) {
              //you have a straight 98765
              bool = 1;
            } else if( ((handdata.match(/8/g) || []).length == 1) && ((handdata.match(/7/g) || []).length == 1) && ((handdata.match(/6/g) || []).length == 1) && ((handdata.match(/5/g) || []).length == 1) && ((handdata.match(/4/g) || []).length == 1)) {
              //you have a straight 87654
              bool = 1;
            } else if( ((handdata.match(/7/g) || []).length == 1) && ((handdata.match(/6/g) || []).length == 1) && ((handdata.match(/5/g) || []).length == 1) && ((handdata.match(/4/g) || []).length == 1) && ((handdata.match(/3/g) || []).length == 1)) {
              //you have a straight 76543
              bool = 1;
            } else if( ((handdata.match(/6/g) || []).length == 1) && ((handdata.match(/5/g) || []).length == 1) && ((handdata.match(/4/g) || []).length == 1) && ((handdata.match(/3/g) || []).length == 1) && ((handdata.match(/2/g) || []).length == 1)) {
              //you have a straight 65432
              bool = 1;
            } else if( ((handdata.match(/5/g) || []).length == 1) && ((handdata.match(/4/g) || []).length == 1) && ((handdata.match(/3/g) || []).length == 1) && ((handdata.match(/2/g) || []).length == 1) && ((handdata.match(/A/g) || []).length == 1)) {
              //you have a straight 5432A
              bool = 1;
            } else {
              bool = 0; //no straight
            }
            return bool;
          }
          function checkFlush(suitedata){ //this function returns 1 on a flush
              var bool = 0;
              if(suitedata.indexOf('SSSSS') ==-1){
                //not all spades
              } else if(suitedata.indexOf('HHHHH') == -1){
                //not all spades
              } else if(suitedata.indexOf('DDDDD') == -1){
                  //not all diamonds
              } else if(suitedata.indexOf('CCCCC') == -1){
                  //not all clubs
              } else {
                //This is a flush
                bool = 1;
              }
              return bool;
          }
          function checkFourOfAKind(handdata){
            //check if there is a 4 of a kind and return 1 if true
            var bool = 0;
              
            if((handdata.match(/A/g) || []).length != 4){
               //no 4 aces
            } else if ((handdata.match(/K/g) || []).length != 4){
              // no 4 2's
            } else if ((handdata.match(/Q/g) || []).length != 4){
                //no 4 3's
            } else if ((handdata.match(/J/g) || []).length != 4){
                //no 4 4's
            } else if ((handdata.match(/10/g) || []).length != 4){
                //no 4 5's
            } else if ((handdata.match(/9/g) || []).length != 4){
                //no 4 6's
            } else if ((handdata.match(/8/g) || []).length != 4){
                //no 4 7's
            } else if ((handdata.match(/7/g) || []).length != 4){
                //no 4 8's
            } else if ((handdata.match(/6/g) || []).length != 4){
                //no 4 9's
            } else if((handdata.match(/5/g) || []).length != 4){

            } else if ((handdata.match(/4/g) || []).length != 4){
                //no 4 3's
            } else if ((handdata.match(/3/g) || []).length != 4){
                //no 4 3's
            } else if ((handdata.match(/2/g) || []).length != 4){
                //no 4 3's
            } else {
              //There was a four of a kind 
              bool = 1;
            }
            return bool;
          }

          socket.on('clearPlayerSelect',function(){
            clearPlayerSelect();
          });
          socket.on('endGame', function(data){
            //the game has ended, the the winning player has been determined by the server

            if(playerNum == data.playerNum){
              //Hell yeah, you won
              document.getElementById('cardDisplay').innerHTML = "You won with a score of " + data.score;
              document.getElementById('display').innerHTML = "Game over, You have WON! Player number: " + data.playerNum + " with " + data.scoreString;

            } else if(data.playerNum == -1){
              //it's a tie
              document.getElementById('cardDisplay').innerHTML = "It's a tie with a score of " + data.score;
              document.getElementById('display').innerHTML = "Game over, It's a TIE!" 
            } else {
              //you lost scrub
              document.getElementById('cardDisplay').innerHTML = "You had a score of " + score + ", They won with a score of " + data.score;
              document.getElementById('display').innerHTML = "Game over, You have LOST! Player number: " + data.playerNum + " won with " + data.scoreString;
            }
            //reset variables for another round

            hand = [];
            reDrawNum = 0;
            reDrawArray = [];
            roundNum = 0;
            score = 0; 
            document.getElementById('submit').innerHTML = '<a href="http://localhost/COMP484-Project-5/public/play">Play again?</a>';
          });

          socket.on('returnReDraw', function(data){

            console.log('Recieved a redraw!');
            if(data.playerNum == playerNum) {
              //They're my cards!
              console.log('I got some cards for me!');
              console.log(data.subdeck);

              if(reDrawArray[0] == 1){
                hand[0] = data.subdeck.shift();
              }
              if(reDrawArray[1] == 1){
                hand[1] = data.subdeck.shift();
              }
              if(reDrawArray[2] == 1){
                hand[2] = data.subdeck.shift();
              }
              if(reDrawArray[3] == 1){
                hand[3] = data.subdeck.shift();
              }
              if(reDrawArray[4] == 1){
                hand[4] = data.subdeck.shift();
              }

              console.log('Here is my hand ' + hand);
              document.getElementById('display').innerHTML = "Draw Again" ;
              document.getElementById('cardDisplay').innerHTML = hand;
              document.getElementById('card0').innerHTML = '<input type="checkbox" id="cardz0" name="cardz0" value="1">' + hand[0];
              document.getElementById('card1').innerHTML = '<input type="checkbox" id="cardz1" name="cardz1" value="1">' + hand[1];
              document.getElementById('card2').innerHTML = '<input type="checkbox" id="cardz2" name="cardz2" value="1">' + hand[2];
              document.getElementById('card3').innerHTML = '<input type="checkbox" id="cardz3" name="cardz3" value="1">' + hand[3];
              document.getElementById('card4').innerHTML = '<input type="checkbox" id="cardz4" name="cardz4" value="1">' + hand[4];
            
              if(roundNum<roundsAllowed){
                document.getElementById('submit').innerHTML = '<input type="submit" value="Submit">';
              } else {
                document.getElementById('submit').innerHTML = '';
                calculateScores();
              }
            
 
              reDrawArray = [0,0,0,0,0];
            } else {
              //ain't my cards
              console.log('Those are not the cards you are looking for...');
            }
          });

          socket.on('newPlayer', function(data){
            console.log('New Player!');
            if(playerNum == 0){
              playerNum = data;
              console.log('I am player ' + playerNum);
              
            } 
          });

          socket.on('gameStart', function(data){
            console.log('Game Start!');
            console.log('I recieved subdeck with');

            console.log(data.subdeck);
            
            //divide up subdeck recieved from the server based on the player number

            if(playerNum == 1){
              for(var i = 0;i<5;i++){
                hand[i] = data.subdeck[i];
              }
            } else if(playerNum == 2){
              var temp = 0;
              for(var i = 5;i<10;i++){
                hand[temp]= data.subdeck[i];
                temp++;
              }
            } else if(playerNum == 3){
              var temp = 0;
              for(var i = 10;i<15;i++){
                hand[temp]= data.subdeck[i];
                temp++;
              }
            } else if(playerNum == 4){
              var temp = 0;
              for(var i = 15;i<20;i++){
                hand[temp]= data.subdeck[i];
                temp++;
              }
            }
            console.log('Here is my hand: ');
            console.log(hand);
            document.getElementById('display').innerHTML = "Game Start";
            document.getElementById('cardDisplay').innerHTML = hand;
            document.getElementById('card0').innerHTML = '<input type="checkbox" id="cardz0" name="cardz0" value="1">' + hand[0];
            document.getElementById('card1').innerHTML = '<input type="checkbox" id="cardz1" name="cardz1" value="1">' + hand[1];
            document.getElementById('card2').innerHTML = '<input type="checkbox" id="cardz2" name="cardz2" value="1">' + hand[2];
            document.getElementById('card3').innerHTML = '<input type="checkbox" id="cardz3" name="cardz3" value="1">' + hand[3];
            document.getElementById('card4').innerHTML = '<input type="checkbox" id="cardz4" name="cardz4" value="1">' + hand[4];
            document.getElementById('submit').innerHTML = '<input type="submit" value="Submit">';
              

          });


        </script>  



    </head>
                  <body>
                    <nav class="navbar navbar-toggleable-md navbar-inverse bg-inverse fixed-top">
                        <button class="navbar-toggler navbar-toggler-right" type="button" data-toggle="collapse" data-target="#navbarsExampleDefault" aria-controls="navbarsExampleDefault" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                        </button>
                        <a class="navbar-brand" href="#">COMP484 Project 5</a>
                      </div>
                    
                    </nav>
                  <div class="container">
                    <div class="row" style="margin-top:10%" >
                      <div class="col-xs-3 col-sm-3 col-md-3"></div>
                      <div class="col-xs-6 col-sm-6 col-md-6">
                        <p id='display'>Not enough players to start</p>
                        <p id='cardDisplay'></p>
                        <form action="javascript:cardDraw()" id="cardForm">
                          <span id="card0"></span>
                          <span id="card1"></span>
                          <span id="card2"></span>
                          <span id="card3"></span>
                          <span id="card4"></span><br>
                          <span id="submit"></span>
                        </form>
                        <form action="javascript:determinePlayerNum()" id="numForm">
                            <input type="checkbox" id="option0" name="option0" value="1">2 Players</input>
                            <input type="checkbox" id="option1" name="option1" value="1">3 Players</input>
                            <input type="checkbox" id="option2" name="option2" value="1">4 Players</input>
                            <input type="submit" id="submit1" value="Submit">'
                        </form>
                      </div>
                      <div class="col-xs-3 col-sm-3 col-md-3"></div>
                    </div>
                  </div>
                </div>
              </body>
            </html>
  
