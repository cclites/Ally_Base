<template>
    <b-row class="justify-content-center">
        <b-col lg="3" md="4" sm="6" xs="12">
            <b-card>
                <div class="text-center">
                    <i class="fa fa-spinner fa-spin fa-3x fa-fw"></i>
                    <p>
                        {{ text }}
                    </p>
                </div>
            </b-card>
        </b-col>
    </b-row>
</template>

<script>
    export default {
        props: {
            text: {
                default: 'Loading Results'
            }
        }
    }
</script>

<style scoped>
    i {
        color: #00aeef;
    }
</style>