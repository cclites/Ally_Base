<template>
    <b-card header="Documents"
        header-bg-variant="info"
        header-text-variant="white"
        >
        <form @submit.prevent="upload()" enctype="multipart/form-data" class="form-inline">
            <div class="form-group">
                <input type="file" name="document" @change="setFile($event.target.files[0])">
            </div>
            <input type="submit" value="Upload" class="btn btn-success">
        </form>
        <hr>
        <table class="table">
            <thead><tr><th>File</th></tr></thead>
            <tbody>
                <tr v-for="document in documents">
                    <td>{{ document.original_filename }}</td>
                </tr>
            </tbody>
        </table>
    </b-card>
</template>
<script>
    import FormDataForm from '../classes/FormDataForm';
    export default {
        props: ['initialDocuments', 'userId'],
        data() {
            return {
                documents: this.initialDocuments,
                file: {},
                form: new Form({
                    file: this.file,
                }),
            };
        },
        methods: {
            setFile(file) {
                this.file = file;
            },
            upload() {
                let formData = new FormData();
                formData.append('file', this.file);
                formData.append('user_id', this.userId);
                let form = new FormDataForm(formData);
                form.setOptions({
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                });
                form.post('/business/documents');
            },
        },
    }
</script>
