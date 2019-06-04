<template>
    <div>
            <b-card header="Text message auto reply settings"
                    header-text-variant="white"
                    header-bg-variant="info"
                    class="pb-3"
            >

                <form @submit.prevent="saveMessaging()">

                  <div class="row">


                      <div class="col-lg-6">
                          <b-form-group label="Auto reply off" label-for="auto_reply_off">
                              <b-form-checkbox
                                      id="auto_reply_off"
                                      name="auto_reply_off"
                                      value="true"
                                      unchecked-value="false"
                              >
                              </b-form-checkbox>
                          </b-form-group>
                          <b-form-group label="Auto reply on indefinitely" label-for="auto_reply_on_indefinitely">
                              <b-form-checkbox
                                      id="auto_reply_on_indefinitely"
                                      name="auto_reply_on_indefinitely"
                                      value="true"
                                      unchecked-value="false"
                              >
                              </b-form-checkbox>
                          </b-form-group>
                      </div>

                      <div class="col-lg-6">
                          <b-form-group label="Auto Reply Message" for="auto_reply_message">

                              <b-form-textarea
                                      id="auto_reply_message"
                                      v-model="auto_reply_message"
                                      placeholder="Enter auto respond message"
                                      rows="3"
                                      max-rows="6"
                              ></b-form-textarea>

                          </b-form-group>
                      </div>

                    </div>

                    <div class="col-md-4">

                        <b-card-sub-title>Week days (Monday-Friday) hours that auto reply is active:</b-card-sub-title>
                        <br><br>

                        <b-form-group label="Start Time" label-for="week-start-time">
                            <time-picker id="week-start-time" v-model="form.week_start_time" placeholder="HH:MM"></time-picker>
                        </b-form-group>

                        <b-form-group label="End Time" label-for="weekend-end-time">
                            <time-picker v-model="form.weekend_end_time" placeholder="HH:MM"></time-picker>
                        </b-form-group>

                        <br>
                        <b-card-sub-title>Weekend days (Saturday &amp; Sunday) hours that auto reply is active:</b-card-sub-title>
                        <br><br>

                        <b-form-group label="Start Time" label-for="weekend-start-time">
                            <time-picker v-model="form.weekend_start_time" placeholder="HH:MM"></time-picker>
                        </b-form-group>

                        <b-form-group label="End Time" label-for="week-end-time">
                            <time-picker v-model="form.week_end_time" placeholder="HH:MM"></time-picker>
                        </b-form-group>

                    </div>

                    <div class="text-right"><button id="save_auto_response_configs" type="button" class="btn btn-success">Save Messaging Options</button></div>

                </form>

            </b-card>
    </div>
</template>

<script>
    export default {

        async mounted() {
            await this.fetchMessagingData();
        },


        computed: {
            //calculateRemainingCharacters(){},   //<-- Might not use
        },

        data() {
            return {
                form: new Form({
                    auto_reply_off: this.auto_reply_off || '',
                    message_characters_remaining : this.message_characters_remaining || 120,
                    auto_reply_on_indefinitely: this.auto_reply_on_indefinitely || '',
                    week_start_time: this.week_start_time || '',
                    week_end_time: this.week_end_time || '',
                    weekend_start_time: this.week_start_time || '',
                    weekend_end_time: this.week_end_time || '',
                    auto_reply_message: this.auto_reply_message || '',
                }),

            }
        },

        methods: {


            async fetchMessagingData(){
                let response = await axios.get('/client/communications/' + this.client.id);
                if (Array.isArray(response.data)) {
                    this.form = response.data;
                } else {
                    this.form = [];
                }
            },

            saveMessaging(){
                const response = axios.post('/client/communications/' + this.form.data())
                                    .then(response => {
                                            this.setItems(response.data);
                                        }).catch(error => {
                                            console.error(error.response);
                                        });
            },

            setItems(data){

            }
        },

        mixins : [Constants],

        name: "ClientCommunicationsTab",

        props: {
            client: {
                type: Object,
                required: true,
            }
        },

        watch: {
            auto_reply_message: function(val){
                if(val.length >= smsLength){
                   this.form.auto_reply_message = val.substring(0, smsLength);
                }
            }
        }
    }
</script>

<style scoped>

</style>