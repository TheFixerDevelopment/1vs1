### 1vs1 plugin by Minifixio, edited by Zeao.

### Description:
Do you want to make 1vs1 matches on your MCPE server? Then this plugin is for you!

## Features
-> Multi arenas system
-> Auto queue management
-> Statistics signs


### How to use:
-> First, you'll need to create your(s) arena(s) doing /arena where your arena is. The players will spawn where you set /area 1 (pos1) and /area 2 (pos2) (see example below). You can make an unlimited numbers of arenas. All the arenas’s positions are saved in data.yml file.

-> Then, the players can start a duel doing /match, a countdown before the fight will start (only 2 players per arena) and they will be teleported in an arena and they will get a sword, armor and food and all their effects will be removed for fight. The fight lasts 3 minutes by default. (You can configure how long the fight will last in configurations) and at the end of the timer if there is no winners, the duel ends and the players are teleported back to the spawn.

-> You can place a sign and write on the 1st line : « [1vs1] » to have a 1vs1 stats sign with the numbers of active arenas and the number of the players in the queue. The signs refreshes every 5 seconds.

### Technical:
-> After a fight, the players are teleported back to the spawn of the default level server.

-> When a player quit's in a fight, the other opponent win.

-> The arenas and the 1vs1 signs positions are stored in data.yml file.

-> When a player quit's during the start match countdown, the match stops.


### Commands:
-> /match : join the 1vs1 queue (Everyone can use that command)
-> /arena : Create a new arena - OPS only


### Notes:

-> Any remarks? Let us know, by opening a new issue.
