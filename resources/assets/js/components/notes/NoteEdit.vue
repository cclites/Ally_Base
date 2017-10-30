<template>
    <b-card header="Edit Note" header-bg-variant="info" header-text-variant="white">
        <form @submit.prevent="saveNote()" @keydown="form.clearError($event.target.name)">
            <b-row>
                <b-col lg="6">
                    <b-form-group label="Caregiver">
                        <span v-if="note.caregiver">{{ note.caregiver.name }}</span>
                        <span v-else>None</span>
                    </b-form-group>
                    <b-form-group label="Tags" label-for="tags">
                        <b-form-input
                                id="tags"
                                name="tags"
                                type="text"
                                v-model="form.tags"
                                maxlength="32"
                        >
                        </b-form-input>
                        <input-help :form="form" field="tags" text="Tag the note for searching."></input-help>
                    </b-form-group>
                </b-col>
                <b-col lg="6">
                    <b-form-group label="Client">
                        <span v-if="note.client">{{ note.client.name }}</span>
                        <span v-else>None</span>
                    </b-form-group>

                    <b-form-group label="Body" labe-for="body">
                        <b-form-textarea
                                id="body"
                                name="body"
                                :rows="5"
                                v-model="form.body"
                        >
                        </b-form-textarea>
                    </b-form-group>
                </b-col>
            </b-row>
            <b-row>
                <b-col lg="12">
                    <b-button id="save-note" variant="success" type="submit">Update</b-button>
                </b-col>
            </b-row>
        </form>
    </b-card>
</template>

<script>
    export default {
        props: ['note'],

        data() {
            return {
                form: new Form({
                    caregiver_id: this.note.caregiver_id,
                    client_id: this.note.client_id,
                    body: this.note.body,
                    tags: this.note.tags
                })
            }
        },

        methods: {
            saveNote() {
                this.form.put('/notes/' + this.note.id);
            }
        }

    }
</script>
