<template>
    <b-card header="Notes"
            header-bg-variant="info"
            header-text-variant="white"
    >
    <div class="d-flex flex-column">
        <b-btn variant="info" class="mb-3 mr-auto" @click="create()">Add Note</b-btn>

        <b-card v-for="note in localNotes"
                class="mb-3 f-1"
                header-tag="header"
                :key="note.id">
                <div slot="header">
                    <b-row align-h="between">
                        <b-col>
                            <strong>{{ note.title }}</strong> Created By: {{ note.creator.name }}
                        </b-col>
                        <!--<b-col>
                            <div class="text-center">Tags: <span v-if="note.tags">{{ note.tags }}</span><span v-else>None</span></div>
                        </b-col>-->
                        <b-col>
                            <div class="pull-right">{{ formatDateFromUTC(note.created_at) + ' ' + formatTimeFromUTC(note.created_at) }}</div>
                        </b-col>
                    </b-row>
                </div>
            <div class="note-body">{{ note.body }}</div>
        </b-card>

        <b-card v-if="! localNotes.length" class="f-1">
            No notes.
        </b-card>
        
        <b-modal id="noteModal" title="Add Note" v-model="noteModal" size="lg">
            <note-form :client="client" :caregiver="caregiver" :prospect="prospect" :source="source" :note="note" ref="noteForm" :modal="1" />

            <div slot="modal-footer">
               <b-btn variant="default" @click="noteModal=false">Close</b-btn>
               <!-- <b-btn variant="danger" @click="deleteActivity()" v-if="selectedItem.id">Delete</b-btn> -->
               <b-btn variant="info" @click="save()">Save</b-btn>
            </div>
        </b-modal>
    </div>
    </b-card>
</template>

<style lang="scss">
    .note-body {
        white-space: pre-wrap;
        word-wrap: break-word;
    }
</style>

<script>
    import FormatsDates from '../../mixins/FormatsDates';

    export default {
        props: {
            notes: { type: Array, default: [] },
            caregiver: { type: Object, default: () => { return {} } },
            client: { type: Object, default: () => { return {} } },
            prospect: { type: Object, default: () => { return {} } },
            source: { type: Object, default: () => { return {} } },
        },

        mixins: [ FormatsDates ],

        data() {
            return {
                noteModal: false,
                note: {},
                localNotes: [],
            };
        },

        mounted() {

        },

        methods: {
            save() {
                this.$refs.noteForm.submit()
                    .then(note => {
                        this.noteModal = false;
                        if (this.noteBelongsToThisUser(note)) {
                            this.localNotes.unshift(note);
                        }
                        this.note = {update: Math.random()};
                    })
                    .catch(e => {
                        console.log(e);
                    })
            },

            create() {
                this.noteModal = true;
            },

            noteBelongsToThisUser(note) {
                if (this.client.id) {
                    return this.client.id == note.client_id;
                } else if (this.caregiver.id) {
                    return this.caregiver.id == note.caregiver_id;
                } else if (this.prospect.id) {
                    return this.prospect.id == note.prospect_id;
                } else if (this.source.id) {
                    return this.source.id == note.referral_source_id;
                }
            },
        },

        created() {
            this.localNotes = this.notes;
        },
    }
</script>
