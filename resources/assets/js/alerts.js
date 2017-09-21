window.alerts = new Vue({
    el: '#alerts',

    data() {
        return {
            'count': 0,
            'messages': [],
        }
    },

    methods: {
        addMessage(type, message) {
            this.messages.unshift({
                'id': ++this.count,
                'type': type,
                'message': message
            });
        },
    }
});
