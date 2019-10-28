<template>

    <li class="nav-item pr-2">

        <a class="nav-link text-muted text-muted position-relative h-100" style="width: 55px" id="openShiftsDropdown" href="/business/open-shifts" aria-haspopup="true" aria-expanded="false">

            <i class="notification-icon open-shifts-icon"></i>

            <span class="badge badge-danger badge-notifications" v-if="total > 0">{{ total }}</span>

            <b-tooltip target="openShiftsDropdown" placement="left" show title="You have open shifts to view" v-if="showTooltip"></b-tooltip>
        </a>
    </li>
</template>

<script>

    import { mapGetters } from 'vuex';
    export default {

        props : {

        },
        data() {

            return {
            }
        },
        async mounted() {

            await this.$store.dispatch( 'notifications/start' ); // Erik TODO => have this grab new open shifts count
        },

        methods: {

        },

        computed: {

            ...mapGetters({ // Erik TODO => have this grab new open shifts count

                notifications : 'notifications/notifications',
                total         : 'notifications/total',
            }),
            items() {

                let maxTitle = 36;
                let maxDescription = 72;
                return this.notifications.map( ( notification ) => {

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