<template>
    <div>
            <b-card header="Text message auto reply settings"
                    header-text-variant="white"
                    header-bg-variant="info"
                    class="pb-3"
            >

                <form @submit="saveMessaging" name="messaging">

                  <div class="row auto_reply_checkboxes">

                      <div class="col-lg-6">
                          <b-form-group label="Auto reply off" label-for="auto_reply_off">
                              <b-form-checkbox
                                      id="auto_reply_off"
                                      name="auto_reply_off"
                                      v-model="form.auto_reply_off"
                              >
                              </b-form-checkbox>
                          </b-form-group>
                          <b-form-group label="Auto reply on indefinitely" label-for="auto_reply_on_indefinitely">
                              <b-form-checkbox
                                      id="auto_reply_on_indefinitely"
                                      name="auto_reply_on_indefinitely"
                                      v-model="form.auto_reply_on_indefinitely"
                              >
                              </b-form-checkbox>
                          </b-form-group>
                      </div>

                      <div class="col-lg-6">
                          <b-form-group label="Auto Reply Message" for="auto_reply_message">

                              <b-form-textarea
                                      id="auto_reply_message"
                                      name="auto_reply_message"
                                      v-model="form.auto_reply_message"
                                      placeholder="Enter auto respond message"
                                      rows="3"
                                      max-rows="6"
                              ></b-form-textarea>

                          </b-form-group>
                      </div>

                    </div>

                    <div class="col-md-4">

                        <h5>Week days (Monday-Friday) hours that auto reply is active:</h5>


                        <b-form-group label="Start Time" label-for="week-start-time">
                            <time-picker id="week-start-time" :ref="form" v-model="week_start_time" placeholder="Time (Ex. 12:00 PM)"></time-picker>
                        </b-form-group>

                        <b-form-group label="End Time" label-for="week-end-time">
                            <time-picker id="week-end-time" :ref="form" v-model="week_end_time" placeholder="Time (Ex. 12:00 PM)"></time-picker>
                        </b-form-group>

                        <br>
                        <h5>Weekend days (Saturday &amp; Sunday) hours that auto reply is active:</h5>


                        <b-form-group label="Start Time" label-for="weekend-start-time">
                            <time-picker id="weekend-start-time" :ref="form" v-model="weekend_start_time" placeholder="Time (Ex. 12:00 PM)"></time-picker>
                        </b-form-group>

                        <b-form-group label="End Time" label-for="weekend-end-time">
                            <time-picker id="weekend-end-time" :ref="form" v-model="weekend_end_time" placeholder="Time (Ex. 12:00 PM)"></time-picker>
                        </b-form-group>

                    </div>

                    <div class="text-right"><button id="save_auto_response_configs" type="button" class="btn btn-success" @click="saveMessaging()">Save Messaging Options</button></div>

                </form>

            </b-card>
    </div>
</template>

<script>
    import Constants from '../../../mixins/Constants';

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
                    auto_reply_off: this.auto_reply_off || true,
                    auto_reply_on_indefinitely: this.auto_reply_on_indefinitely || '',
                    week_start_time: ('week_start_time' in this.comms) ? this.comms.week_start_time : '',
                    week_end_time: ('week_end_time' in this.comms) ? this.comms.week_end_time : '',
                    weekend_start_time: ('weekend_start_time' in this.comms) ? this.comms.weekend_start_time : '',
                    weekend_end_time: ('weekend_end_time' in this.comms) ? this.comms.weekend_end_time : '',
                    auto_reply_message: this.auto_reply_message || '',
                }),
                week_start_time: '',
                week_end_time: '',
                weekend_start_time: '',
                weekend_end_time: ''

            }
        },

        methods: {


            async fetchMessagingData(){

                console.log("Fetch messaging data");
                let response = await axios.get('/business/clients/' + this.client.id + '/communications/');
                if (Array.isArray(response.data)) {
                    this.comms = response.data;
                }
            },

            saveMessaging(){

                console.log("Saving messaging");

                let params = '?auto_reply_off=' + this.form.auto_reply_off + "&auto_reply_on_indefinitely=" +
                             this.form.auto_reply_on_indefinitely + "&message=" + this.form.auto_reply_message +
                             '&week_start=' + this.form.week_start_time + '&week_end=' + this.form.week_end_time +
                             '&weekend_start=' + this.form.weekend_start_time + '&weekend_end=' + this.form.weekend_end_time;

                console.log(params);


                const response = axios.post('/business/clients/' + this.client.id + '/communications' + params)
                                        .then(response => {
                                            //this.setItems(response.data);
                                            console.log(response);
                                        }).catch(error => {
                                            console.error(error.response);
                                        });
            },

            setItems(data){
              //ToDo
            }
        },

        mixins : [Constants],

        name: "ClientCommunicationsTab",

        props: {
            client: {
                type: Object,
                required: true,
            },

            comms: {
                default() {
                    return {};
                }
            },
        },

        watch: {
            auto_reply_message: function(val){
                if(val.length >= smsLength){
                   this.form.auto_reply_message = val.substring(0, smsLength);
                }
            },
            week_start_time: function(val){

                console.log("Start time val is " + val);

                //week_start_time = week_start_time.format('HH:mm');
                this.form.week_start_time = val;
            },
            week_end_time: function(val){

                console.log("End time val is " + val);

               // week_end_time = week_end_time.format('HH:mm');
                this.form.week_end_time = val;
            },
            weekend_start_time: function(val){
                    this.form.weekend_start_time = val;
            },
            weekend_end_time: function(val){
                this.form.weekend_end_time = val;
            },
        }
    }
</script>

<style scoped>

</style>