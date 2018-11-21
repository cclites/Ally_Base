<template>
    <b-card header="New Note" header-bg-variant="info" header-text-variant="white">
        <note-form ref="noteForm"></note-form>
        <b-row>
            <b-col lg="12">
                <submit-button :submitting="submitting" variant="success" type="button" @click="saveNote()">Create</submit-button>
            </b-col>
        </b-row>
    </b-card>
</template>

<script>
    export default {
        data() {
            return {
                submitting: false,
            }
        },

        methods: {
            async saveNote() {
                this.submitting = true;
                await this.$refs.noteForm.submit();
                this.submitting = false;
            }
        }

    }
</script>
