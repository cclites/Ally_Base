<template>
    <form @submit.prevent="publish()" enctype="multipart/form-data">
        <b-row>
            <b-col lg="6">
                <b-card>
                    <b-form-group label="Type" label-for="type">
                        <b-select name="type" id="type" v-model="form.type">
                            <option value="faq">FAQ</option>
                            <option value="tutorial">Tutorial</option>
                            <option value="resource">Resource</option>
                        </b-select>
                        <input-help :form="form" field="type" text=""></input-help>
                    </b-form-group>

                    <b-form-group label="Title" label-for="title">
                        <b-form-input
                                name="title"
                                type="text"
                                v-model="form.title"
                        >
                        </b-form-input>
                        <input-help :form="form" field="title" text=""></input-help>
                    </b-form-group>

                    <b-form-group label="Slug" label-for="slug">
                        <b-form-input
                                name="slug"
                                type="text"
                                v-model="form.slug"
                        >
                        </b-form-input>
                        <input-help :form="form" field="slug" text=""></input-help>
                    </b-form-group>

                    <b-form-group label="Body HTML" label-for="body">
                        <b-textarea id="body"
                                    :rows="10"
                                    v-model="form.body"
                        />
                        <input-help :form="form" field="body" text="" />
                    </b-form-group>

                    <b-form-group label="Video" label-for="video_file">
                        <div v-if="form.video_attachment_id">
                            <label>{{ form.video.filename }}</label>
                            <b-btn variant="danger" size="sm" class="ml-2" @click="removeVideo()">
                                <i class="fa fa-times"></i>
                            </b-btn>
                        </div>
                        <div v-else>
                            <label>None</label>
                            <input id="video_file" name="video_file" type="file" @change="uploadVideo" hidden>
                            <b-btn variant="info" @click.stop="openFileDialog('video_file')" class="ml-4" :disabled="busyUploading">Upload Video</b-btn>
                        </div>
                    </b-form-group>

                    <hr />

                    <div class="mt-3 mb-3">
                        <div v-if="form.attachments.length < 4">
                            <input id="attachment" name="attachment" type="file" @change="upload" hidden>
                            <b-btn variant="info" class="pull-right" @click.stop="openFileDialog()" :disabled="busyUploading">Upload File</b-btn>
                        </div>

                        <h3>Attachments</h3>
                        <div class="mt-3">
                            <div v-if="form.attachments.length == 0" class="text-center">No Attachments</div>
                            <b-row v-else v-for="(item, index) in form.attachments" :key="index" class="mb-3">
                                <b-col sm="1">{{ index + 1 }}</b-col>
                                <b-col sm="11">
                                    <a :href="item.url">{{ item.name }}</a>
                                    <b-btn variant="danger" size="sm" class="ml-2" @click="removeAttachment(item.id)">
                                        <i class="fa fa-times"></i>
                                    </b-btn>
                                </b-col>
                            </b-row>
                        </div>
                    </div>
                </b-card>
            </b-col>
            <b-col lg="6">
                <b-card>
                    <knowledge-item :item="form" />
                </b-card>
                <div class="mt-4">
                    <b-btn variant="success" size="lg" type="submit" :disabled="busyUploading">Publish</b-btn>
                </div>
            </b-col>
        </b-row>
    </form>
</template>

<script>
    import FormatsDates from '../../mixins/FormatsDates';

    export default {
        mixins: [FormatsDates],

        props: ['knowledge'],

        data() {
            return {
                files: [],
                form: new Form({
                    type: '',
                    title: '',
                    slug: '',
                    body: '',
                    youtube_id: '',
                    updated_at: '',
                    attachments: [],
                    video_attachment_id: '',
                    video: {},
                }),
                busyUploading: false,
            };
        },

        computed: {
            mode() {
                return this.knowledge.id ? 'edit' : 'create';
            }
        },

        methods: {
            publish() {
                let url = `/admin/knowledge-manager`;
                let method = 'post';

                if (this.mode == 'edit') {
                    url = url + `/${this.knowledge.id}`;
                    method = 'patch';
                }

                this.form.submit(method, url)
                    .then( ({ data }) => {
                        console.log(data);
                        if (this.mode == 'edit') {
                            this.form = new Form(data.data);
                        }
                    })
                    .catch(e => {
                        console.log(e);
                    })
            },

            slugify(str) {
                str = str.replace(/^\s+|\s+$/g, ''); // trim
                str = str.toLowerCase();

                // remove accents, swap ñ for n, etc
                var from = "àáäâèéëêìíïîòóöôùúüûñç·/_,:;";
                var to   = "aaaaeeeeiiiioooouuuunc------";
                for (var i=0, l=from.length ; i<l ; i++) {
                    str = str.replace(new RegExp(from.charAt(i), 'g'), to.charAt(i));
                }

                str = str.replace(/[^a-z0-9 -]/g, '') // remove invalid chars
                    .replace(/\s+/g, '-') // collapse whitespace and replace by -
                    .replace(/-+/g, '-'); // collapse dashes

                return str;
            },

            openFileDialog(elementId = 'attachment') {
                document.getElementById(elementId).click();
            },

            upload(e) {
                let file = e.target.files[0];
                if (! file) {
                    console.log('no file');
                    return;
                }

                let f = new Form({
                    attachment: file,
                });

                this.busyUploading = true;

                f.submit('post', `/admin/knowledge-manager/attachments`, true)
                    .then( ({ data }) => {
                        this.form.attachments.push(data.data);
                        this.resetUpload(e.target);
                    })
                    .catch(e => {
                        console.log(e);
                        this.resetUpload(e.target);
                    });
            },

            uploadVideo(e) {
                let file = e.target.files[0];
                if (! file) {
                    console.log('no file');
                    return;
                }

                let f = new Form({
                    attachment: file,
                });

                this.busyUploading = true;

                f.submit('post', `/admin/knowledge-manager/video`, true)
                    .then( ({ data }) => {
                        this.form.video = data.data;
                        this.form.video_attachment_id = data.data.id;
                        this.resetUpload(e.target);
                    })
                    .catch(e => {
                        console.log(e);
                        this.resetUpload(e.target);
                    });
            },

            resetUpload(target) {
                this.busyUploading = false;
                target.value = null;
            },

            removeAttachment(id) {
                this.form.attachments = this.form.attachments.filter(obj => obj.id !== id);
            },

            removeVideo() {
                this.form.video = {};
                this.form.video_attachment_id = '';
            },
        },

        mounted() {
            if (this.mode === 'edit') {
                this.form = new Form(this.knowledge);
            }
        },

        watch: {
            'form.title': function(newVal) {
                if (newVal !== this.knowledge.title) {
                    this.form.slug = this.slugify(this.form.title);
                }
            },
        },
    }
</script>
