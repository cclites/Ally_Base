<template>
        <b-card header="Tax Documents"
                header-bg-variant="info"
                header-text-variant="white">

            <div v-for="field in fields" :key="field" class="mb-3">
                <h4 class="chat-text">{{ field }}</h4>
                <b-row v-for="item in items[field]" :key="item.id">
                    <b-col md="2">
                        {{ item.name }}
                    </b-col>
                    <b-col md="1">
                        <a :href=" '/business/business-1099/download/' + item.id ">Download 1099</a>
                    </b-col>

                </b-row>
            </div>

        </b-card>
</template>

<script>
    export default {
        name: "Caregiver1099sTab",

        props: {
            caregiver: ''
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
                axios.get('/business/caregiver-1099/' + this.caregiver)
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