<template>
    <div>
        <b-list-group>
            <b-list-group-item v-for="type in types" :key="type.id" class="d-flex">
                <div class="f-1">{{ type.type }}</div>
                <div class="d-flex justify-content-end align-items-center">
                    <a href="#" style="margin-right:10px"><i class="fa fa-edit" @click="showNew(type)"></i></a>
                    <a href="#"><i class="fa fa-trash" @click="showDestroy(type)"></i></a>
                </div>
            </b-list-group-item>
            <b-list-group-item button @click="showNew()">
                <i class="fa fa-plus mr-2"></i>Add Expiration Type
            </b-list-group-item>
        </b-list-group>

        <b-modal ref="addExpirationTypeModal" :title="`Add Expiration Type`" @ok="addExpiration" @cancel="hideNew()" @shown="focus" ok-variant="info">
            <b-form-group label="Expiration Type">
                <b-form-input id="newType" ref="newType" v-model="form.type"></b-form-input>
            </b-form-group>
        </b-modal>

        <b-modal ref="destroyExpirationTypeModal" :title="`Remove default expiration type?`" @ok="destroyExpiration" @cancel="hideDestroy()" ok-variant="info">
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
                editing: false,
                busy: false,
            }
        },

        methods: {
            async fetchChainExpirations() {
                await axios.get(`/business/expiration-types`)
                    .then(({data}) => {
                        this.types = data;
                    })
                    .catch(e => {
                    });
            },

            showNew( type = null ) {
                this.editing = null;

                if( type ){

                    this.editing = type;
                    this.form.type = type.type;
                }
                this.$refs.addExpirationTypeModal.show();
            },

            focus(e) {
                this.$refs.newType.$el.focus();
            },

            hideNew() {
                this.editing = null;
                this.$refs.addExpirationTypeModal.hide();
            },

            showDestroy(type) {
                this.type = type;
                this.$refs.destroyExpirationTypeModal.show();
            },
            hideDestroy() {
                this.$refs.destroyExpirationTypeModal.hide();

            },

            addExpiration() {

                this.busy = true;

                let method = this.editing ? 'patch' : 'post';
                let url = '/business/expiration-types' + ( this.editing ? '/' + this.editing.id : '' );

                this.form.submit( method, url )
                    .then(({data}) => {
                        this.form.type = '';
                        this.setItems(data.data);
                    })
                    .catch(e => {})
                    .finally(() => {
                        this.busy = false;
                        this.editing = null;
                    });
            },

            destroyExpiration() {
                this.busy = true;
                let url = '/business/expiration-types/' + this.type.id;
                this.form.submit('DELETE', url)
                    .then(({data}) => {
                        let index = this.types.findIndex(x => x.id == this.type.id);
                        Vue.delete(this.types, index);
                    })
                    .catch(e => {
                    })
                    .finally(() => {
                        this.busy = false;
                    });
            },

            setItems(data) {
                this.types = data;
                this.hide();
                this.type = '';
            }
        },

        async mounted() {
            this.loading = true;
            await this.fetchChainExpirations();
            this.loading = false;
        },
    }
</script>
