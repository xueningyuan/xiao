<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Document</title>
</head>
<body>
    <div id="app">
        <ul>
            <li v-for="v in messages">@{{ v.content }}</li>
        </ul>
        <div><textarea v-model="content"></textarea></div>
        <div><input @click="send" type="button" value="发送"></div>
    </div>
</body>
</html>
<script src="/vue.min.js"></script>
<script src="/axios.min.js"></script>
<script>
new Vue({
    el:"#app",
    data:{
        ws:null,
        content:'',
        messages:[]
    },
    created: function(){
        this.ws = new WebSocket('ws://127.0.0.1:9999')
        this.ws.onmessage=this.message
        axios.get('/api/messages')
            .then((res)=>{
                this.messages=res.data
            })
    },
    methods:{
        message:function(e){
            console.log( e.data )
            this.messages.push({content:e.data})
        },
        send:function(){
            if(this.content != '')
            {
                var params = new URLSearchParams()
                params.append('content', this.content)
                axios.post('/api/messages', params)
                this.content = ''
            }
        }
    }
})
</script>