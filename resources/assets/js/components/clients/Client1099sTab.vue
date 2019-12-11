<template>
        <b-card header="Tax Documents"
                header-bg-variant="info"
                header-text-variant="white">

            <div v-if="fields.length">
                <div v-for="field in fields" :key="field" class="mb-3">
                    <h4 class="chat-text">{{ field }}</h4>
                    <b-row v-for="item in items[field]" :key="item.id">
                        <b-col md="4">
                            {{ item.name }}
                        </b-col>
                        <b-col md="4">
                            <a :href=" '/client/client-1099/download/' + item.id ">Download 1099</a>
                        </b-col>

                    </b-row>
                </div>
            </div>
            <div v-else>
                There are no records to display.
            </div>

            <hr>

            2018 and prior years would have been mailed to you and are not available electronically.

        </b-card>
</template>

<script>
    export default {
        name: "Client1099sTab",

        props: {
            client: ''
        },

        data() {
            return {
                items: [],
                fields: [],
            }
        },

        mounted(){
            this.load1099s();
        },

        methods:{
            load1099s(){
                axios.get('/client/client-1099/' + this.client)
                    .then(response => {
                        this.items = response.data;
                        this.fields = Object.keys(this.items);
                    })
                    .catch( e => {})
                    .finally(() => {});
            }
        }
    }
</script>

<style scoped>

</style>