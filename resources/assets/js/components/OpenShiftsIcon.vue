<template>
    <li class="nav-item dropdown pr-2">
        <a class="nav-link dropdown-toggle text-muted text-muted" id="notificationsDropdown" href="" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> <i class="fa fa-bell notification-icon"></i>
            <span class="badge badge-danger badge-notifications" v-if="total > 0">{{ total }}</span>
            <b-tooltip target="notificationsDropdown" placement="left" show title="You have notifications that require action" v-if="showTooltip"></b-tooltip>
        </a>
        <div class="dropdown-menu dropdown-menu-right mailbox scale-up">
            <ul>
                <li>
                    <div class="drop-title">Notifications</div>
                </li>
                <li>
                    <div class="message-center">
                        <!-- Message -->
                        <a v-for="item in items" :href="'/business/notifications/' + item.id" :key="item.id">
                            <div class="btn btn-danger btn-circle"><i class="fa fa-link"></i></div>
                            <div class="mail-content">
                                <h5>{{ item.title }}</h5> <span class="mail-desc">{{ item.message }}</span> <span class="time">{{ item.time }}</span>
                            </div>
                        </a>
                        <a href="javascript:void(0);" v-if="!notifications.length">
                            <b>No notifications</b>
                        </a>
                    </div>
                </li>
                <li>
                    <a class="nav-link text-center" href="/business/notifications"> <strong>View all notifications</strong> <i class="fa fa-angle-right"></i> </a>
                </li>
            </ul>
        </div>
    </li>
</template>

<script>
    import { mapGetters } from 'vuex';
    export default {
        data() {
            return {
            }
        },

        async mounted() {
            await this.$store.dispatch('notifications/start');
        },

        methods: {
        },

        computed: {
            ...mapGetters({
                notifications: 'notifications/notifications',
                total: 'notifications/total',
            }),
            items() {
                let maxTitle = 36;
                let maxDescription = 72;
                return this.notifications.map((notification) => {
                    let data = JSON.parse(JSON.stringify(notification));
                    if (data.message.length > maxDescription) {
                        data.message = data.message.substring(0, maxDescription) + '..';
                    }
                    if (data.title.length > maxTitle) {
                        data.title = data.title.substring(0, maxTitle) + '..';
                    }
                    data.time = moment.utc(data.created_at).local().format('LT');
                    return data;
                })
            },
            showTooltip() {
                // Suppressed always temporarily
                return false;
                return this.notifications.length && window.location.pathname === '/business/schedule';
            }
        }
    }
</script>

<style>
    .badge-notifications {
        position: absolute;
        top: 18px;
        left: 34px;
    }
    .mdi-message {
        font-size: 24px;
        margin-right: -7px;
        padding-left: 7px;
    }
</style>