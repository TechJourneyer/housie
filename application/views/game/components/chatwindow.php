
<link rel="stylesheet" href="/assets/css/game/chatwindow.css" >
<div class='chat-popup ' id="chatWindow" >
    <form class="form-container">
        <div class="content">
            <div class="card">
                <div class="card-header" ng-click="toggleChat()">Chat
                    <span class="float-right">
                        <button  title="Close chat window"  class="btn btn-sm btn-danger">
                            <i class="fa  fa-times" aria-hidden="true"></i>
                        </button>
                    </span>
                </div>
                <div class="card-body">
                    <ul class="chat-list p-2">
                        <li ng-repeat='chat in group_chats track by $index' class="{{ (chat.uid == user.uid) ? 'out' : 'in' }}">
                            <div class="chat-img">
                                <img ng-if='chat.photoURL!== undefined' alt="Avtar" src="{{chat.photoURL}}">
                                <img ng-if='chat.photoURL == undefined' alt="Avtar" src="/assets/images/user_icon.webp">
                            </div>
                            <div class="chat-body">
                                <div class="chat-message">
                                    <h5>{{chat.displayName}}</h5>
                                    <p class=''>{{chat.text}}</p>
                                </div>
                            </div>
                        </li>
                    </ul>
                    <textarea placeholder="Type message.." name="msg" id='text_message' rows='2' ></textarea>
                    <div class=" text-center">
                        <button  title="Send Msg" ng-click="sendMessage()" class="btn btn-sm btn-primary">
                            <i class="fa fa-2x fa-paper-plane" aria-hidden="true"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>    
</div>

<!-- <button class="open-button" onclick="openForm()">Chat</button> -->
<span class="open-button" ng-click="toggleChat()">
    <span class='badge badge-dark p-2'><strong>{{ (group_chats_msgs - group_chats_seen_msgs) }}</strong></span>
    <img class='chat-button' height='70px' src="/assets/images/chat.png"/>
</span>