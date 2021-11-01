<!DOCTYPE html>
<html lang="en">
<head>
  <title>Swoole Socket Chat!</title>
  <link rel="stylesheet" type="text/css" href="https://unpkg.com/primer-css@9.6.0/build/build.css">
</head>
<body>
  <div class="container">
    <div class="blankslate my-4">
      <h3>Swoole Socket Chat!</h3>
      <p>Super chatty chat over here.</p>
    </div>
    <div class="columns" id="app">
      <div class="one-half column">
        <div class="border p-3 mb-3 border-gray" v-bind:class="colors[m.client]" v-for="m in messages" v-bind:key="m.id">
          <span><strong v-text="m.username + ':'"></strong></span>
          <span v-text="m.message"></span>
        </div>
        <form v-on:submit.prevent="sendMessage">
          <dl class="form-group">
            <dt><label>Username</label></dt>
            <dd><input class="form-control" type="text" placeholder="Got2Joe..." v-model="username"></dd>
          </dl>
          <dl class="form-group">
            <dt><label>Message</label></dt>
            <dd><input class="form-control" type="text" placeholder="Say hello..." v-model="message"></dd>
          </dl>
          <div class="form-group">
            <span class="input-group-button">
              <button type="submit" class="btn">Send</button>
            </span>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script src="https://unpkg.com/vue@2.5.16/dist/vue.js" defer></script>
  <script defer>
    document.addEventListener('DOMContentLoaded', function () {
      new Vue({
        el: '#app',
        data: {
          username: '',
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
          this.socket = new WebSocket('ws://' + window.location.host);

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
              this.messages.push(data);
              this.loadedMessages.push(data.id);
            }
          });
        },
        methods: {
          sendMessage() {
            this.socket.send(JSON.stringify({
              username: this.username,
              message: this.message
            }));
            this.message = '';
          }
        }
      });
    });
  </script>
</body>
</html>