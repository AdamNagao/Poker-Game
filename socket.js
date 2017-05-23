let app = require('http').createServer(); // create HTTP server
let io = require('socket.io')(app, {path: '/socket.io'}); // bind Socket to HTTP server
app.listen(3000); // listen on port 3000
console.log('Listening for connections on port 3000');

var playerCount = 0;
var scoreReceivedNum =0;
var highestScore = 0;
var winningPlayerNum = 0;
var numOfPlayers = -1;
var deck = ["AS", "AH","AD", "AC","2S","2H","2D","2C","3S","3H","3D","3C","4S","4H","4D","4C","5S","5H","5D","5C",
"6S","6H","6D","6C","7S","7H","7D","7C","8S","8H","8D","8C","9S","9H","9D","9C","10S","10H","10D","10C",
"JS","JH","JD","JC","QS","QH","QD","QC","KS","KH","KD","KC"];
var subdeck =[];


io.on('connection', function(socket) {
  // console.log('Socket connected');
   socket.join('my-room'); // join the socket into the room called 'my-room'
   socket.in('my-room').emit('fromServer', {id: 'foo'}); // send to all clients in room

   socket.on('fromClient', function(data) { // listen for fromClient message
   	  playerCount++;
      console.log('Player has connected, Total: ' + playerCount); // single client has connected


      io.emit('newPlayer', playerCount); 

      if(playerCount == numOfPlayers){
      	console.log('Game Start!');
      	gameStart();
      } else {
      	console.log('Not Enough players to start..');
      }
   });

   socket.on('determinePlayerNum', function(data){
      console.log('Number of Players ' + data.num)
      numOfPlayers = data.num;
       if(playerCount == numOfPlayers){
      	console.log('Game Start!');
      	gameStart();
      } else {
      	console.log('Not Enough players to start..');
      }
   });

   socket.on('requestReDraw', function(data) { // listen requestredraw from a client

   	var pNum = data.playerNum;

   	console.log('Player Number ' + data.playerNum + ' has requested ' + data.reDrawNum +'cards'); // a client has requested some cards
 	
 	subdeck =[];

 	for(var i = 0; i < data.reDrawNum ;i++){
      	var rand = Math.floor(Math.random() * 52);
      	//console.log('Here is my random number ' +rand );
      	if(deck[rand] != -1){
      		subdeck[i] = deck[rand];
      		console.log('Drew card ' + deck[rand]);
      		deck[rand] = -1; //remove used card from deck
      	} else {
      		//we drew a card that no longer exists, try again
      		i--;
      	}
     }
     
     console.log('Client ' + pNum + ' redraw request serviced');
	 io.emit('returnReDraw', {subdeck: subdeck, playerNum:pNum});
   });

   socket.on('scoreCalculated', function(data) { // listen requestredraw from a client
	console.log('Recieved player number ' + data.playerNum + ' score of ' + data.score);

	scoreReceivedNum++;

	if(data.score > highestScore){
		highestScore = data.score;
		winningPlayerNum = data.playerNum;
	} else if(data.score == highestScore){
		//There is a tie
		highestScore = data.score
		winningPlayerNum = -1;
	} else if(highestScore > data.score){
		
	}

	if(scoreReceivedNum == playerCount){
		//Received all the scores from the clients, end the game. winningPlayerNum won with highestScore
		//reset some values for the next round of the
		playerCount = 0;
		scoreReceivedNum = 0; 

		io.emit('endGame', {score: highestScore, playerNum:winningPlayerNum, scoreString:data.scoreString});
		console.log('Player Number ' + winningPlayerNum + ' won with a score of ' + highestScore);

	}

   });

});

function gameStart(){
    //deal out some cards

    io.emit('clearPlayerSelect');
    for(var i = 0; i <(playerCount*5);i++){
      	var rand = Math.floor(Math.random() * 52);
      	//console.log('Here is my random number ' +rand );
      	if(deck[rand] != -1){
      		subdeck[i] = deck[rand];
      		console.log('Drew card ' + deck[rand]);
      		deck[rand] = -1; //remove used card from deck
      	} else {
      			//we drew a card that no longer exists, try again
      		i--;
      	}
      } 	
    io.emit('gameStart', {subdeck: subdeck});  // send to all clients in room but this shit is broken  
}


