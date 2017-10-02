<template>
    <div class="card">
        <div class="card-body">
            <div class="d-flex flex-row">
                <div :class="iconscheme"><i :class="icon"></i></div>
                <div class="m-l-10 align-self-center">
                    <h3 class="m-b-0 font-light">{{ value }}</h3>
                    <h5 class="text-muted m-b-0">{{ text }}</h5></div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        props: {
            'icon': {
                default() {
                    return 'fa fa-bar-chart';
                }
            },
            'text': {
                default() {
                    return '';
                }
            },
            'value': {
                default() {
                    return '0';
                }
            },
            'variant': {
                default() {
                    return 'info';
                }
            },
        },

        computed: {
            iconscheme() {
                return 'round round-lg align-self-center round-' + this.variant;
            }
        }
    }
</script>