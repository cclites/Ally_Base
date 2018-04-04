<template>
    <b-modal title="Create a Manual Shift" v-model="showModal" size="lg" class="modal-fit-more">
        <b-container fluid>
            <business-shift 
                :activities="activities"
                :caregiver="caregiver"
                :client="client"
            ></business-shift>
        </b-container>
        <div slot="modal-footer">
            <slot name="buttons"></slot>
        </div>
    </b-modal>
</template>

<script>
    export default {
        data() { 
            return {
                activities: [],
            }
        },

        props: {
            value: {},
            items: Array,
            caregiver: {},
            client: {},
        },

        computed: {
            showModal: {
                get() {
                    return this.value;
                },
                set(value) {
                    this.$emit('input', value);
                }
            },
        },

        methods: {
            loadData() {
                axios.get('/business/activities')
                    .then(response => {
                        console.log('fetched activities');
                        if (Array.isArray(response.data)) {
                            this.activities = response.data;
                        }
                        else {
                            this.activities = [];
                        }
                    }).catch(e => {
                        console.log('axios error:');
                        console.log(e);
                    });
            },
        },

        mounted() {
            this.loadData();
        },
    }
</script>
