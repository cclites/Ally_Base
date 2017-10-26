<template>
    <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle text-muted text-muted" href="" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> <i class="mdi mdi-message"></i>
            <div class="notify" v-if="notifications.length"> <span class="heartbit"></span> <span class="point"></span> </div>
        </a>
        <div class="dropdown-menu dropdown-menu-right mailbox scale-up">
            <ul>
                <li>
                    <div class="drop-title">Exceptions</div>
                </li>
                <li>
                    <div class="message-center">
                        <!-- Message -->
                        <a v-for="item in items" :href="'/business/exceptions/' + item.id">
                            <div class="btn btn-danger btn-circle"><i class="fa fa-link"></i></div>
                            <div class="mail-content">
                                <h5>{{ item.title }}</h5> <span class="mail-desc">{{ item.description }}</span> <span class="time">{{ item.time }}</span>
                            </div>
                        </a>
                        <a href="javascript:void(0);" v-if="!notifications.length">
                            <b>No active exceptions</b>
                        </a>
                    </div>
                </li>
                <li>
                    <a class="nav-link text-center" href="/business/exceptions"> <strong>View all exceptions</strong> <i class="fa fa-angle-right"></i> </a>
                </li>
            </ul>
        </div>
    </li>
</template>

<script>
    export default {
        data() {
            return {
                'notifications': [],
            }
        },

        mounted() {
            this.loadNotifications();
            setInterval(this.loadNotifications, 30000);
        },

        methods: {
            loadNotifications() {
                let component = this;
                axios.get('/business/exceptions')
                    .then(function(response) {
                        component.notifications = response.data;
                    });
            }
        },

        computed: {
            count() {
                return this.notifications.length;
            },
            items() {
                let maxTitle = 36;
                let maxDescription = 72;
                return this.notifications.map(function(notification) {
                    if (notification.description.length > maxDescription) {
                        notification.description = notification.description.substring(0, maxDescription) + '..';
                    }
                    if (notification.title.length > maxTitle) {
                        notification.title = notification.title.substring(0, maxTitle) + '..';
                    }
                    notification.time = moment.utc(notification.created_at).local().format('LT');
                    return notification;
                })
            }
        }
    }
</script>