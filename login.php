<!DOCTYPE html>
<html lang="en">
<head>
    <title>swoole在线客服极简版</title>
    <link rel="stylesheet" type="text/css" href="https://unpkg.com/primer-css@9.6.0/build/build.css">
</head>
<body>
<div class="container">
    <div class="blankslate my-4">
        <h3 class="mb-2">登陆</h3>
        <p>输入用户名或者密码登陆，管理员以admin开头，普通用户以user开头，密码必须与用户名相同</p>
    </div>
    <div class="columns" id="app">
        <div class="one-half column">
            <form v-on:submit.prevent="login">
                <dl class="form-group">
                    <dt><label>用户名</label></dt>
                    <dd><input class="form-control" type="text" placeholder="adminXXX或userXXX" v-model="username"></dd>
                </dl>
                <dl class="form-group">
                    <dt><label>密码</label></dt>
                    <dd><input class="form-control" type="text" placeholder="与用户名相同" v-model="password"></dd>
                </dl>
                <div class="form-group">
            <span class="input-group-button">
              <button type="submit" class="btn">登陆</button>
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
                password: '',
            },
            mounted() {
            },
            methods: {
                login() {
                    if (this.username.startsWith('admin') && this.username.length > 5 && this.username == this.password) {
                        window.location.href = '/admin?username=' + this.username;
                    } else if (this.username.startsWith('user') && this.username.length > 4 && this.username == this.password) {
                        window.location.href = '/user?username=' + this.username;
                    } else {
                        alert("用户名或密码必须以admin或user开头，且密码必须与用户名相同")
                    }
                }
            }
        });
    });
</script>
</body>
</html>