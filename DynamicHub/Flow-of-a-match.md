Flow of a Match
===


## Stage: `OPEN`
- [x] **Initiation**: a new match will be created at the `OPEN` stage after this process is completed:
    - [x] owner module actively counts the number of open matches (implemented in DynamicHub API). If lower than a value (provided by implementor),
    - [x] retrieve a new unique (integer) match ID from the database for this match asynchronously
    - [x] create a new match for joining after ID has been received from database
- [x] players joining
    - [x] gamers _must not_ join the match as a player after it is completely full, or when it is not at the `OPEN` state
    - [x] any players _can_ join the match (with specified permission node from MatchBaseConfig)
    - [x] privileged players _can_ join the match when it is semi-full (with permission node from MatchBaseConfig)
    - [x] anyone, including admins, who is not in the module that owns the match, _must not_ be able to join the match
    - [x] a newly joined player teleports to a position of the match defined by MatchBaseConfig
- [x] spectators joining
    - [x] spectators _should_ not affect gameplay, so make them invisible
    - [x] spectators _can_ join at _any_ stage of the match except the `FINALIZING` or `GARBAGE` states
- [ ] **Termination**: the match will automatically change to `PREPARING` stage:
    - [x] if a certain time limit is reached after the match is open and the least number of players is continuously being reached
    - [x] _or_ if the strict limit (not semi-full limit) of the number of players have been reached
    
## Stage: `PREPARING`
- [x] **Initialization**:
    - [x] Teleport players (optionally) to a "preparing room". Players may be invisible or visible to other players and spectators in the same match. (Implementation's responsibility)
    - [x] Gamers in other matches may use the same room, but they will be mutually invisible.
- [x] options
    - [x] The match _can_ allow (all or certain) players (but _not encouraged to_ allow spectators, as they may casually leave and join random matches) to vote, or in any forms, vote for game rules such as the map to play in, certain rules, etc. The game is responsible to do this, not DynamicHub API.
- [x] **Termination**: the match will automatically change to `LOADING` stage:
    - [x] after a certain time limit (provided by MatchBaseConfig, cannot be renewed) has been reached
    - [x] triggered by the match that preparation has been completed
- [x] **Finalization**:
    - [x] Requires the match to provide a [`ThreadedMapProvider`](src/DynamicHub/Module/Match/MapProvider/ThreadedMapProvider.php) that creates an immediately loadable level for playing.
        - [x] The following default map provider frameworks are provided:
            - [x] Folder - copy a world from another folder
            - [x] Online - download a world from the Internet (must be cURL-compatible) and extract as ZIP, TAR or any formats supported by the PharData class
            - [x] Zip - extract a world from a zip file on the disk

## Stage: `LOADING`
- [x] **Initiation**
    - [x] After the match has entered this stage, it will not do anything (players will stay at preparation room) until _both_ of these two conditions have been met:
        - [x] The map from the finalization of `PREPARING` is _ready_ (but _not_ loaded)
        - [x] The number of matches at the `RUNNING` stage is lower than a certain number (provided by the owner module, _not_ MatchBaseConfig because this is global) (note that a match should stay at `RUNNING` stage until it has unloaded its associated level, but not necessarily cleaned it)
    - [x] The map downloaded will be loaded. If it cannot be loaded, the match will be terminated.
    - [x] Players and spectators will be teleported into positions inside the map provided by the match
- [x] **Termination**
    - [x] Change to `RUNNING` hen a certain time limit has passed, as this part is mainly for loading chunks. Do not confuse this stage or the `PREPARATION` stage with the concept of "resource gathering" in Walls servers. The game should only take place within the `RUNNING` stage, as only matches that are `RUNNING` are considered to be having a game.

## Stage: `RUNNING`
- [x] **Initialization**
    - [x] Free all players, initialize all blocks or mechanisms that may be hidden in `LOADING`. This part is mainly implemented by the underlying implementation.
- [ ] Implementors must be able to provide a definite time limit for this match to run during the `RUNNING` stage. The time limit _must_ not be exceeded, as it will be displayed to gamers of other matches that are pending for a slot to run.
- [ ] kicked players
    - [ ] players may be kicked from the match if they have lost. In that case, they leave the match as a player, and _may_ join the match as a normal spectator (implementation-dependent).
- [x] **Termination**
    - [x] Implementors will be reminded when the strict time limit ends. The implementor _must_ end the match immediately. Do _not_ confuse this stage with the concept of "death match" in many survival games servers. The game should only take place within the `RUNNING` stage (of course, except irrelevant things like parkour during waiting).
- [x] **Finalization**: unload the level after teleporting away the players and spectators

## Stage: `FINALIZING`
- [ ] **Initiation**: This stage should only compile the results of this match, such as to announce who won the match, and to flush logs of the game. No more changes should be done to the game. This is considered as an independent stage only because this may take more than one tick (some servers may want winners to stand on a "prize stage", for example). The match should enter the `GARBAGE` stage as soon as possible.

## Stage: `GARBAGE`
- [x] This is the stage when a match is considered as a "garbage". Only the owner module should have access to matches at this stage, and the owner module should immediately dispose instances of `GARBAGE` matches whenever scanned a match at this stage.

> Note: Initiation means why this stage would be started. Initialization means what to do when this stage starts. Finalization means what to do when this stage ends. Termination means why this stage would be ended.
