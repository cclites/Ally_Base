<template>
    <div>
        <b-list-group>
            <b-list-group-item v-for="type in types" :key="type.id" class="d-flex">
                <div class="f-1">{{ type.type }}</div>
                <div class="ml-auto">
                    <!--i class="fa fa-trash-alt"></i-->
                    <a href="#"><i class="fa fa-trash" @click="showDestroy(type)"></i></a>
                </div>
            </b-list-group-item>
            <b-list-group-item button @click="showNew()">
                <i class="fa fa-plus mr-2"></i>Add Expiration Type
            </b-list-group-item>
        </b-list-group>

        <b-modal ref="addExpirationTypeModal" :title="`Expiration type`" @ok="addExpiration" @cancel="hideNew()" @shown="focus" ok-variant="info">
            <b-form-group label="Default Expiration Type">
                <b-form-input id="newType" ref="newType" v-model="new_type"></b-form-input>
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
                new_type: '',
                form: new Form({
                }),
                busy: false,
            }
        },

        methods: {
            async fetchChainExpirations() {
                await axios.get(`/business/expiration-types?manage=true`)
                    .then(({data}) => {
                        this.types = data;
                    })
                    .catch(e => {
                    });
            },

            showNew(){
                this.$refs.addExpirationTypeModal.show();
            },

            focus(e){
                this.$refs.newType.$el.focus();
            },

            hideNew(){
                this.$refs.addExpirationTypeModal.hide();
            },

            showDestroy(type){
                this.type = type;
                this.$refs.destroyExpirationTypeModal.show();
            },
            hideDestroy(){
                this.$refs.destroyExpirationTypeModal.hide();

            },



            addExpiration(){

                let url = `/business/expiration-types/store/` + this.new_type;

                this.form.submit('POST', url)
                    .then( ({ data }) => {
                        this.setItems(data.data);
                    })
                    .catch(e => {})
                    .finally(() => { this.busy = false; });

            },
            destroyExpiration(){

                let url = '/business/expiration-types/destroy/' + this.type.id;

                this.form.submit('DELETE', url)
                    .then( ({ data }) => {
                        this.setItems(data.data);
                    })
                    .catch(e => {})
                    .finally(() => { this.busy = false; });
            },
            setItems(data){
                this.types = data;
                this.hide();
                this.type = '';
                this.new_type = '';
            }
        },

        async mounted() {
            this.loading = true;
            await this.fetchChainExpirations();

            this.loading = false;
        },
    }
</script>

<style scoped>

</style>