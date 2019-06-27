<template>
    <div>
        <b-list-group>
            <b-list-group-item v-for="type in sortedTypes" :key="type.id" class="d-flex">
                <div class="f-1">{{ type.type }}</div>
                <div class="ml-auto">
                    <a href="#"><i class="fa fa-trash" @click="showDestroy(type)"></i></a>
                </div>
            </b-list-group-item>
            <b-list-group-item button @click="showModal = true">
                <i class="fa fa-plus mr-2"></i>Add Expiration Type
            </b-list-group-item>
        </b-list-group>

        <b-modal v-model="showModal" :title="`Add default expiration type`" @ok="add" @cancel="showModal = false" ok-variant="info">
            <b-form-group label="Default Expiration Type">
                <b-form-input v-model="form.type"></b-form-input>
            </b-form-group>
        </b-modal>

        <b-modal ref="destroyExpirationTypeModal" :title="`Remove default expiration type?`" @ok="destroy()" @cancel="hideDestroy()" ok-variant="info">
            <b-form-group>
               {{ type.type }}
            </b-form-group>
        </b-modal>
    </div>
</template>

<script>
    import Form from '../../../classes/Form';

    export default {
        name: "DefaultExpirationsManager",

        data() {
            return {
                types: [],
                type: '',
                form: new Form({
                    type: '',
                }),
                busy: false,
                showModal: false,
            }
        },

        computed: {
            sortedTypes() {
                return this.types.sort((a, b) => {
                    var typeA = a.type.toLowerCase(), typeB = b.type.toLowerCase();
                    if (typeA < typeB) {
                        return -1;
                    }
                    if (typeA > typeB) {
                        return 1;
                    }
                    return 0;
                });
            },
        },

        methods: {
            async fetch() {
                await axios.get(`/business/expiration-types`)
                    .then(({data}) => {
                        this.types = data;
                    })
                    .catch(e => {
                    });
            },

            add() {
                this.form.post(`/business/expiration-types`)
                    .then(({data}) => {
                        this.types.splice(0, 0, data.data);
                        this.form.reset();
                    })
                    .catch(e => {
                    })
                    .finally(() => {
                    });
            },

            destroy() {
                let form = new Form({});
                form.submit('DELETE', `/business/expiration-types/${this.type.id}`)
                    .then( ({ data }) => {
                        this.types = this.types.filter(x => x.id != this.type.id);
                    })
                    .catch(e => {
                    })
                    .finally(() => {
                    });
            },

            showDestroy(type) {
                this.type = type;
                this.$refs.destroyExpirationTypeModal.show();
            },

            hideDestroy() {
                this.$refs.destroyExpirationTypeModal.hide();
            },

            setItems(data) {
                this.types = data;
                this.hide();
                this.type = '';
            }
        },

        async mounted() {
            this.loading = true;
            await this.fetch();
            this.loading = false;
        },
    }
</script>

<style scoped>

</style>