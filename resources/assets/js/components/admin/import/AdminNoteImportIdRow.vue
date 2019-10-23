<template>
    <tr>
        <td>{{ model.rowNo }}</td>
        <td><input type="text" class="form-control" v-model=" model.title " /></td>
        <td><input type="text" class="form-control" v-model=" model.type " /></td>
        <td :id="`clientIdentifier${index}`">{{ identifiers.client_name }}</td>
        <td>
            <b-popover :show.sync="clientPopover"
                       :target="`clientIdentifier${index}`"
                       placement="top"
            >
                <template slot="title">
                    Client Mapping <b-button size="sm" @click="emitCreateClient()">Create a New Client</b-button>
                </template>
                <div class="form-group" v-if="clientPopover">
                    <select2 class="form-control" v-model="model.client_id" ref="client">
                        <option v-for="client in clients" :value="client.id" :key="client.id">{{ client.nameLastFirst }}</option>
                    </select2>
                </div>
                <div class="form-group">
                    <b-button variant="info" @click="saveMapping('client')">Save Mapping</b-button>
                    <b-button variant="default" @click="clientPopover = false">Close</b-button>
                </div>
            </b-popover>
            <span class="red bold" v-if="!model.client_id">Unmapped</span>
            <span v-else>{{ mappedClientName }}</span>
            <b-btn @click="clientPopover = !clientPopover" variant="info" size="sm"><i class="fa fa-edit"></i></b-btn>
            <b-btn @click=" swapNames( model ) " variant="info" size="sm"><i class="fa fa-exchange"></i></b-btn>
        </td>
        <td :id="`cgIdentifier${index}`">{{ identifiers.caregiver_name }}</td>
        <td>
            <b-popover :show.sync="caregiverPopover"
                       :target="`cgIdentifier${index}`"
                       placement="top"
            >
                <template slot="title">
                    Caregiver Mapping <b-button size="sm" @click="emitCreateCaregiver()">Create a New Caregiver</b-button>
                </template>
                <div class="form-group" v-if="caregiverPopover">
                    <select2 class="form-control" v-model="model.caregiver_id" ref="caregiver">
                        <option v-for="caregiver in caregivers" :value="caregiver.id" :key="caregiver.id">{{ caregiver.nameLastFirst }}</option>
                    </select2>
                </div>
                <div class="form-group">
                    <b-button variant="info" @click="saveMapping('caregiver')">Save Mapping</b-button>
                    <b-button variant="default" @click="caregiverPopover = false">Close</b-button>
                </div>
            </b-popover>
            <span class="red bold" v-if="!model.caregiver_id">Unmapped</span>
            <span v-else>{{ mappedCaregiverName }}</span>
            <b-btn @click="caregiverPopover = !caregiverPopover" variant="info" size="sm"><i class="fa fa-edit"></i></b-btn>
        </td>
        <td><input type="text" class="form-control" v-model="model.body" /></td>
        <td><input type="text" class="form-control" v-model="model.tags" /></td>
        <td><input type="text" class="form-control short" v-model="model.created_by" /></td>
    </tr>
</template>

<script>

    import FormatsNumbers from "../../../mixins/FormatsNumbers";

    export default {
        mixins: [FormatsNumbers],

        props: {
            index: Number,
            note: Object,
            identifiers: Object,
            clients: Array,
            caregivers: Array,
        },

        data() {
            return {
                'model': this.note,
                'clientPopover': false,
                'caregiverPopover': false,
                'mappedClientName': this.getNameById(this.clients, this.note.client_id),
                'mappedCaregiverName': this.getNameById(this.caregivers, this.note.caregiver_id),
            }
        },

        mounted() {

        },

        methods: {

            swapNames( model ){

                this.$emit( 'swapIdentifiers', model.rowNo );
            },
            getNameById(array, id) {
                let index = array.findIndex(item => item.id == id);
                if (index < 0) return "";
                return array[index].nameLastFirst;
            },

            async saveMapping(type) {
                let id = this.model[`${type}_id`];
                let name = this.identifiers[`${type}_name`];

                // POST to REST endpoint
                const form = new Form({ id, name });
                const response = await form.post('/admin/import/map/' + type);

                this.$emit('mappedIdentifier', type, name, id);

                // Close popover
                if (this[`${type}Popover`]) {
                    this[`${type}Popover`] = false;
                }
            },

            emitCreateClient() {
                this.clientPopover = false;
                this.$emit('createClient', this.identifiers.client_name);
            },

            emitCreateCaregiver() {
                this.caregiverPopover = false;
                this.$emit('createCaregiver', this.identifiers.caregiver_name);
            }
        },

        computed: {

        },

        watch: {

            'model.client_id': function(val) {
                this.mappedClientName = this.getNameById(this.clients, val);
            },
            'model.caregiver_id': function(val) {
                this.mappedCaregiverName = this.getNameById(this.caregivers, val);
            },
            caregivers() {
                this.mappedCaregiverName = this.getNameById(this.caregivers, this.model.caregiver_id);
            },
            clients() {
                this.mappedClientName = this.getNameById(this.clients, this.model.client_id);
            }
        }
    }
</script>

<style>
    .select2-dropdown {
        z-index: 9999;
    }
    .form-control.short {
        width: 75px;
        padding-right: 3px;
    }
    .red { color: red }
    .bold { font-weight: bold; }
    .popover { max-width: 400px; }
</style>
