<template>
    <b-card
            header="Admin 1099"
            header-text-variant="white"
            header-bg-variant="info"
    >
        <div v-for="year in years" :key="year" class="mb-3">
            <h4 lg="12">{{ year }}:</h4>
            <ul>
                <li @click="download1099( year )">
                    1099 Export CSV
                </li>
                <li @click="getEmails( year, 'caregiver')">
                    Get a list of all Caregiver Emails that were in this 1099
                </li>
                <li @click="getEmails( year, 'client')">
                    Get a list of all Client Emails that were in this 1099
                </li>
            </ul>
        </div>

        <b-row v-if="! years.length">
            {{ emptyText }}
        </b-row>

        <b-modal v-model="emailModal"
                 @cancel="hideModal()"
                 ok-variant="info"
                 size="lg"
                 :title="modalTitle"
        >
            <b-textarea v-model="emails" id="emails" class="mb-2"></b-textarea>

            <b-button variant="info"
                      @click="copy()"
                      class="mb-2 float-right"
                      title="Copy to Clipboard">
                <i class="fa fa-copy"></i>Copy to Clipboard
            </b-button>
        </b-modal>
    </b-card>
</template>

<script>
    export default {
        name: "Caregiver1099Admin",
        props: {
            years: {
                type: Array,
                default: []
            },
        },
        data(){
            return {
                emailModal: false,
                modalTitle: '',
                emails: '',
                emptyText: 'There is no 1099 information in the database.',
            }
        },
        mounted(){
        },
        methods: {
            download1099(year){

                let url = '/admin/business-1099/transmit/' + year;

                axios.get(url)
                    .then(response => {
                        let csv = response.data;

                        var hiddenElement = document.createElement('a');
                        hiddenElement.href = 'data:text/csv;charset=utf-8,' + encodeURI(csv);
                        hiddenElement.target = '_blank';
                        hiddenElement.download = 'Transmission_Report.csv';
                        hiddenElement.click();

                        this.transmitSelected = [];
                        this.generate();
                    })
                    .catch( e => {})
                    .finally(() => {
                    });
            },

            getEmails(year, role){
                let url = '/admin/business-1099/user-emails/' + year + '/' + role;
                this.emailModal = true;
                this.modalTitle = _.capitalize(role) + " emails for " + year;

                axios.get(url)
                    .then(response => {
                        this.emails = response.data;
                    })
                    .catch( e => {})
                    .finally(() => {
                    });
            },

            copy(){
                var copyText = document.querySelector("#emails");
                copyText.select();
                document.execCommand("copy");
            },

            hideModal(){
                this.emailModal = false;
            },
        }
    }
</script>

<style scoped>
    ul{
        list-style-type: none;
    }

    li{
        cursor: pointer;
    }

    li:hover{
        text-decoration: underline;
    }
</style>