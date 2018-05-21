<template>
    <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle text-muted text-muted" id="notificationsDropdown" href="" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> <i class="mdi mdi-message"></i>
            <span class="badge badge-danger badge-notifications" v-if="notifications.length">{{ notifications.length }}</span>
            <b-tooltip target="notificationsDropdown" placement="left" show title="You have exceptions that need your action!" v-if="showTooltip"></b-tooltip>
        </a>
        <div class="dropdown-menu dropdown-menu-right mailbox scale-up">
            <ul>
                <li>
                    <div class="drop-title">Exceptions</div>
                </li>
                <li>
                    <div class="message-center">
                        <!-- Message -->
                        <a v-for="item in items" :href="'/business/exceptions/' + item.id" :key="item.id">
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
                axios.get('/business/exceptions?json=1')
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
            },
            showTooltip() {
                return this.notifications.length && window.location.pathname === '/business/schedule';
            }
        }
    }
</script>

<style>
    .badge-notifications {
        position: absolute;
        top: 18px;
        left: 32px;
    }
    .mdi-message {
        font-size: 24px;
        margin-right: -7px;
        padding-left: 7px;
    }
</style>