// Require the packages we will use:
const http = require("http"),
      fs = require("fs");

const port = 3456;
const file = "client.html";
// Listen for HTTP connections.  This is essentially a miniature static file server that only serves our one file, client.html, on port 3456:
const server = http.createServer(function (req, res) {
    // This callback runs when a new connection is made to our HTTP server.

    let url = req.url    // get the url of the request
    if( url.split("/")[1] === "public" ) {
        handlePublic(url, req, res)    // run seperated code if the request contanis public/ in the first of it
    }
    else {
        fs.readFile(file, function (err, data) {
            // This callback runs when the client.html file has been read from the filesystem.
            if (err) return res.writeHead(500);
            res.writeHead(200);
            res.end(data);
        });
    }
});

// For CSS to show up on page correctly
function handlePublic(url, req, res) {
    let file = fs.readFileSync("./" + url)  // which will send any file from public/
    res.end( file )
}

// Function to get the socket id of a socket by its nickname
function getIdFromNickname(map, value) {
    for (let [key, val] of map.entries()) {
        if (val == value) {
            return key;
        }
    }
}

// To get all the users in a given room
function getUsersInRoom(roomname) {
    const ofRooms = io.of("/").adapter.rooms;
    let usersInNewRoom = ofRooms[roomname];
    let usersInNewRoomArray = Object.entries(usersInNewRoom);
    let userNicknames = new Set();
        
    for (const user of usersInNewRoomArray) {
        let userId = user[0];
        userNicknames.add(users.get(userId));
    }
    let serializedNicknames = [...userNicknames];
    return serializedNicknames;
}

server.listen(port);

// Import Socket.IO and pass our HTTP server object to it.
const socketio = require("socket.io")(http, {
    wsEngine: 'ws'
});

// Map of socket ids to nicknames
let users = new Map();

// map of rooms with passwords to their passwords
let pwd_rooms = new Map();

// map of rooms to their creators
let users_rooms = new Map();

//map of users and rooms they are banned from
let banned_list = new Map();

const io = socketio.listen(server);
io.sockets.on("connection", function (socket) {
    let nickname;
    let currentRoom;

    // Function to leave a current room and join a new room
    function leaveAndJoin(oldRoom, newRoom, data) {
        if (data['currRoom'] != undefined) {
            let oldRoom = data['currRoom'];
            socket.leave(oldRoom);
    
            let oldRoomUsers = new Set(data['currRoomUsers']);
            oldRoomUsers.delete(nickname);
            let serializedRoomUsers = [...oldRoomUsers];
            let leavee = users.get(socket.id);

            io.to(oldRoom).emit("socket_left_room", { message: nickname + " left " + oldRoom, room_users: serializedRoomUsers, socket_left: leavee });
        }
    
        socket.join(newRoom);
        currentRoom = newRoom;
        let adminId = users_rooms.get(newRoom);
        let admin = users.get(adminId);
    
        let serializedNicknames = getUsersInRoom(newRoom);
        let joinee = users.get(socket.id);
        io.to(data['newRoom']).emit("socket_joined_room", { message: nickname + " joined " + newRoom, room: newRoom, room_users: serializedNicknames, admin: admin, socket_joined: joinee });
    }

    // To ensure no two sockets share the same username (does not consider case)
    socket.on("verify_username", (data) => {
        nickname = data['username'];
        for (let [id, name] of users.entries()) {
            if (name == nickname) {
                io.to(socket.id).emit("nickname_taken", { taken: true });
                return;
            }
        }
        io.to(socket.id).emit("nickname_taken", {taken: false, username: nickname });
        
    });

    // Displays a message that a user joined and adds their nickname to the users map
    socket.on("user_joined_message", (data) => {
        console.log(data['username'] + " has joined the chat");
        nickname = data['username'];
        users.set(socket.id, nickname);
    });

    // Sends message from socket to all other sockets in the same room
    socket.on('message_to_server', (data) => {
        // This callback runs when the server receives a new message from the client.
        console.log("message: " + data["message"]); // log it to the Node.JS output
        currentRoom = data['currRoom'];
        io.sockets.to(currentRoom).emit("message_to_client", { message: data["message"], username: nickname }) // broadcast the message to other users
    });

    // Creates a room 
    socket.on("create-room", (data) => {
        // Records room's password if exists
        if (data['pwd'] != undefined) {
            pwd_rooms.set(data['room'], data['pwd']);
        } else if (users_rooms.get(data['room']) != undefined) {
            io.to(socket.id).emit("room_name_taken", { message: "This room name is already taken. Please try another name." });
            return;
        } else {
            console.log("room created: " + data["room"]);
        }
        users_rooms.set(data['room'], socket.id);
        io.sockets.emit("create_room_message", {message: data["room"] });
    });

    // Allows a socket to leave one room and join another
    socket.on("change-room", (data) => {

        let newRoom = data['newRoom'];
        let oldRoom = data['oldRoom'];

        // Ensures socket is not banned from room before joining
        if (banned_list.get(socket.id)?.has(newRoom)) {
            io.to(socket.id).emit("banned_from_room_message", { message: "You cannot join " + newRoom + " as you have been banned from it." })
            return;
        }
        
        // Checks if room has a password
        if (data['roomPwd'] != undefined) {
            let newRoomPwd = pwd_rooms.get(data['newRoom']);

            // Verifies password
            if (data['roomPwd'] == newRoomPwd) {
                leaveAndJoin(oldRoom, newRoom, data);
            } else {
                io.to(socket.id).emit("wrong_room_password");
            }
        } else if (pwd_rooms.has(newRoom) && users_rooms.get(newRoom) != socket.id) {
            io.to(socket.id).emit("get_room_password", { room: newRoom });
        } else {
            leaveAndJoin(oldRoom, newRoom, data);
        }
    });

    // Allows a socket to send a private message to another socket
    socket.on("private_msg", (data) => {
        let receiverId = getIdFromNickname(users, data['receiver']);
        io.to(receiverId).emit("private_msg_received", { message: data['sender'] + " (private message): " + data['message'], sender: data['sender'] });
    });

    // Allows a room creator to kick someone from their room
    socket.on("kick_user_action", (data) => {

        let room_admin = data['room_admin'];
        let kickedSocketId = getIdFromNickname(users, data['user_kicked']);
        let kickedSocket = Object.values(io.sockets.sockets).find(socket => socket.id == kickedSocketId);

        kickedSocket.leave(data['room']);
        kickedSocket.join("Main Lobby");

        let usersInRoom = getUsersInRoom(data['room']);
        let usersInMainLobby = getUsersInRoom("Main Lobby");


        io.to(data['room']).emit("kicked_room_message", { message: data['user_kicked'] + " was kicked from the room!", room_users: usersInRoom, admin: room_admin });
        io.to(kickedSocketId).emit("kicked_user_message", { message: "You have been kicked from " + data['room'], room_users: usersInMainLobby });
    });

    // Allows a room creator to ban someone from their room
    socket.on("ban_user_action", (data) => {

        let room_admin = data['room_admin'];
        let bannedSocketId = getIdFromNickname(users, data['user_banned']);
        let bannedSocket = Object.values(io.sockets.sockets).find(socket => socket.id == bannedSocketId);

        bannedSocket.leave(data['room']);
        bannedSocket.join("Main Lobby");

        if (banned_list.get(bannedSocketId) == undefined) {
            banned_list.set(bannedSocketId, new Set());
        }
        banned_list.get(bannedSocketId).add(data['room']);

        let usersInRoom = getUsersInRoom(data['room']);
        let usersInMainLobby = getUsersInRoom("Main Lobby");

        io.to(data['room']).emit("kicked_room_message", { message: data['user_banned'] + " was banned from the room!", room_users: usersInRoom, admin: room_admin });
        io.to(bannedSocketId).emit("kicked_user_message", { message: "You have been banned from " + data['room'], room_users: usersInMainLobby });
    });

    // Allows a socket to send a room invite to another socket in its current room
    socket.on("send_room_invite", (data) => {
        let room = data['room'];
        let receiver = data['receiver'];
        let sender = data['sender'];

        if (users_rooms.has(room)) {
            let receiverId = getIdFromNickname(users, receiver);
            io.to(receiverId).emit("room_invite_received", { message: "You have received an invite from " + sender + " to join " + room, room_name: room })
        } else {
            return;
        }
    });

    // Allows a room creator to clear the chat of their room
    socket.on("clear_chat_req", (data) => {
        io.to(data['room']).emit("clear_chat_res")
    })
});

console.log("Successfully launched chat server");
