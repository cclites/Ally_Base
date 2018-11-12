<template>
    <b-card>
        <div class="mb-3">
            <label for="role_filter" class="mr-2">Filter For Role:</label>
            <b-select v-model="role_filter" style="width:auto" id="role_filter">
                <option value="">-- Show All --</option>
                <option value="caregiver">Caregiver</option>
                <option value="client">Client</option>
                <option value="office_user">Office User</option>
            </b-select>
            <b-btn class="pull-right" variant="primary" href="/admin/knowledge-manager/create">Add Item</b-btn>
        </div>

        <h3>FAQs</h3>
        <div class="table-responsive">
            <b-table hover
                     sort-by="title"
                     :items="faq"
                     :fields="fields"
                     show-empty
            >
                <template slot="title" scope="row">
                    {{ truncate(row.item.title, 50) }}
                </template>
                <template slot="actions" scope="row">
                    <b-btn size="sm" :href="'/admin/knowledge-manager/' + row.item.id" variant="info" v-b-tooltip.hover title="Edit">
                        <i class="fa fa-edit"></i>
                    </b-btn>
                    <b-btn size="sm" @click="destroy(row.item)" variant="danger" v-b-tooltip.hover title="Delete">
                        <i class="fa fa-times"></i>
                    </b-btn>
                </template>
            </b-table>
        </div>

        <h3>Tutorials</h3>
        <div class="table-responsive">
            <b-table hover
                     sort-by="title"
                     :items="tutorials"
                     :fields="fields"
                     show-empty
            >
                <template slot="title" scope="row">
                    {{ truncate(row.item.title, 35) }}
                </template>
                <template slot="actions" scope="row">
                    <b-btn size="sm" :href="'/admin/knowledge-manager/' + row.item.id" variant="info" v-b-tooltip.hover title="Edit">
                        <i class="fa fa-edit"></i>
                    </b-btn>
                    <b-btn size="sm" @click="destroy(row.item)" variant="danger" v-b-tooltip.hover title="Delete">
                        <i class="fa fa-times"></i>
                    </b-btn>
                </template>
            </b-table>
        </div>

        <h3>Resources</h3>
        <div class="table-responsive">
            <b-table hover
                     sort-by="title"
                     :items="resources"
                     :fields="fields"
                     show-empty
            >
                <template slot="title" scope="row">
                    {{ truncate(row.item.title, 35) }}
                </template>
                <template slot="actions" scope="row">
                    <b-btn size="sm" :href="'/admin/knowledge-manager/' + row.item.id" variant="info" v-b-tooltip.hover title="Edit">
                        <i class="fa fa-edit"></i>
                    </b-btn>
                    <b-btn size="sm" @click="destroy(row.item)" variant="danger" v-b-tooltip.hover title="Delete">
                        <i class="fa fa-times"></i>
                    </b-btn>
                </template>
            </b-table>
        </div>
    </b-card>
</template>

<script>
    import FormatsDates from '../../mixins/FormatsDates';

    export default {
        mixins: [FormatsDates],

        props: ['knowledgeBase'],

        data() {
            return {
                role_filter: '',
                items: [],
                fields: {
                    title: {},
                    updated_at: {
                        key: 'updated_at',
                        label: 'Date Modified',
                        formatter: item => this.formatDateFromUTC(item),
                    },
                    actions: {}
                },
            };
        },

        computed: {
            faq() {
                return this.items.filter(item => item.type === 'faq');
            },

            tutorials() {
                return this.items.filter(item => item.type === 'tutorial');
            },

            resources() {
                return this.items.filter(item => item.type === 'resource');
            },
        },

        methods: {
            truncate(string, chars = 50) {
                if (string.length > chars) {
                    return string.substr(0, chars) + '...';
                }

                return string;
            },

            destroy(item) {
                let form = new Form();
                if (confirm('Are you sure you want to delete the item "' + item.title + '" ?')) {
                    form.submit('delete', '/admin/knowledge-manager/' + item.id)
                        .then( ({ data }) => {
                            this.items = data.data;
                        })
                        .catch(e => {

                        })
                }
            },
        },

        mounted() {
            this.items = this.knowledgeBase;
        },

        watch: {
            role_filter(newVal) {
                if (newVal == '') {
                    this.items = this.knowledgeBase;
                    return;
                }

                this.items = this.knowledgeBase.filter(obj => obj.assigned_roles.includes(newVal));
            },
        },
    }
</script>
