### 1vs1 plugin by Minifixio, edited by Zeao, fixed by NickTehUnicorn.

### Description:
Do you want to make 1vs1 matches on your MCPE server? Then this plugin is for you!

## Features
-> Multi-arena system

-> Auto queue management

-> Statistics signs


### How to use:
-> First, you'll need to create your arena(s) by using: /arena create where your arena is. The players will spawn where you set /area 1 (pos1) and /area 2 (pos2) (see example below). You can make an unlimited numbers of arenas. All the arenas positions are saved in data.yml file.

-> Then, the players can start a duel doing /match, a countdown before the fight will start (only 2 players per arena) and they will be teleported in an arena and they will get a sword, armor and food. Also, all their effects will be removed for fight. The fight lasts 3 minutes by default. (You can configure how long the fight will last in configurations) and at the end of the timer if there is no winners, the duel ends and the players are teleported back to the spawn.

-> You can place a sign and write « [1vs1] » on the 1st line to have a 1vs1 stats sign with the numbers of active arenas and the number of the players in the queue. The signs refreshes every 5 seconds.

### Technical:
-> After a fight, the players are teleported back to the spawn of the default level server.

-> When a player quits in a fight, the other opponent wins.

-> The arenas and the 1vs1 signs' positions are stored in data.yml file.

-> When a player quits during the start match countdown, the match stops.


### Commands:
-> /match : Join the 1vs1 queue (Everyone can use that command)

-> /arena : Arena command usage

-> /arena 1 : Set positon 1 in your arena (Where you're standing.)

-> /arena 2 : Set positon 2 in your arena (Where you're standing.)

-> /arena create : Creates a new arena - OPS ONLY

### Notes:

-> Any remarks? Let us know, by opening a new issue.
