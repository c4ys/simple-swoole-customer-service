<!DOCTYPE html>
<html lang="en">
<head>
    <title>swoole在线客服极简版</title>
    <link rel="stylesheet" type="text/css" href="https://unpkg.com/primer-css@9.6.0/build/build.css">
</head>
<body>
<div class="container">
    <div class="columns" id="app">
        <div class="blankslate my-4">
            <h3 class="mb-2">用户界面</h3>
            <p>当前登录用户：{{username}}</p>
        </div>
        <div class="mx-auto col-8">
            <form v-on:submit.prevent="sendMessage" class="mb-3">
                <div class="form-group">
                    <input class="form-control " type="text" placeholder="消息内容..." v-model="message">
                    <span class="input-group-button">
                        <button type="submit" class="btn">Send</button>
                    </span>
                </div>
            </form>
            <div class="border p-3 mb-3 border-gray" v-bind:class="colors[m.client]" v-for="m in messages"
                 v-bind:key="m.id">
                <span><strong v-text="m.username + ':'"></strong></span>
                <span v-text="m.message"></span>
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/vue@2.5.16/dist/vue.js" defer></script>
<script defer>
    document.addEventListener('DOMContentLoaded', function () {
        new Vue({
            el: '#app',
            data: {
                username: '<?=$username?>',
                message: '',
                loadedMessages: [],
                messages: [],
                socket: null,
                colors: [
                    'text-blue',
                    'text-red',
                    'text-green',
                    'text-orange',
                    'text-purple'
                ]
            },
            mounted() {
                // Create WebSocket connection.
                this.socket = new WebSocket('ws://' + window.location.host + "?username=" + this.username);

                this.socket.addEventListener('open', (e) => {
                    console.log(e);
                });

                this.socket.addEventListener('close', (e) => {
                    console.log(e);
                });

                this.socket.addEventListener('error', (e) => {
                    console.log(e);
                });

                // Listen for messages and push to our array
                this.socket.addEventListener('message', (event) => {
                    const data = JSON.parse(event.data);
                    // make sure the messages are unique
                    if (this.loadedMessages.indexOf(data.id) < 0) {
                        this.messages.unshift(data);
                        this.loadedMessages.push(data.id);
                    }
                });
            },
            methods: {
                sendMessage() {
                    if (this.message) {
                        this.socket.send(JSON.stringify({
                            username: this.username,
                            message: this.message
                        }));
                        this.message = '';
                    }
                }
            }
        });
    });
</script>
</body>
</html>