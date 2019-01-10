<template>
    <b-card header="Add Note" header-bg-variant="info" header-text-variant="white">
        <note-form ref="noteForm"></note-form>
        <b-row>
            <b-col lg="12" >
                <div class="text-right">
                    <b-btn :disabled="submitting" variant="success" type="button" @click="saveNote()">Create</b-btn>
                </div>
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
                try {
                    await this.$refs.noteForm.submit();
                } catch (e) {}
                this.submitting = false;
            }
        }

    }
</script>
