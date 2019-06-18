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
                          <b-form-group label="Auto reply off" label-for="auto_off">
                              <b-form-checkbox
                                      id="auto_off"
                                      name="auto_off"
                                      v-model="form.auto_off"
                              >
                              </b-form-checkbox>
                          </b-form-group>
                          <b-form-group label="Auto reply on indefinitely" label-for="on_indefinitely">
                              <b-form-checkbox
                                      id="on_indefinitely"
                                      name="on_indefinitely"
                                      v-model="form.on_indefinitely"
                              >
                              </b-form-checkbox>
                          </b-form-group>
                      </div>

                      <div class="col-lg-6">
                          <b-form-group label="Auto Reply Message" for="message" label-class="required">
                              <b-form-textarea
                                      id="message"
                                      name="message"
                                      v-model="form.message"
                                      placeholder="Enter auto respond message"
                                      rows="3"
                                      max-rows="6"
                                      required
                              ></b-form-textarea>

                          </b-form-group>
                      </div>

                    </div>

                    <div class="col-md-4">

                        <h5>Week days (Monday-Friday) hours that auto reply is active:</h5>


                        <b-form-group label="Start Time" label-for="week-start-time" label-class="required">
                            <time-picker id="week-start-time" v-model="form.week_start"></time-picker>
                        </b-form-group>

                        <b-form-group label="End Time" label-for="week-end-time" label-class="required">
                            <time-picker id="week-end-time" v-model="form.week_end"></time-picker>
                        </b-form-group>

                        <br>
                        <h5>Weekend days (Saturday &amp; Sunday) hours that auto reply is active:</h5>


                        <b-form-group label="Start Time" label-for="weekend-start-time" label-class="required">
                            <time-picker id="weekend-start-time" v-model="form.weekend_start"></time-picker>
                        </b-form-group>

                        <b-form-group label="End Time" label-for="weekend-end-time" label-class="required">
                            <time-picker id="weekend-end-time" v-model="form.weekend_end"></time-picker>
                        </b-form-group>

                    </div>

                    <div class="text-right"><button id="save_auto_response_configs" type="button" class="btn btn-success" @click="saveMessaging()">Save Messaging Options</button></div>

                </form>

            </b-card>
    </div>
</template>

<script>
    import Constants from '../../mixins/Constants';

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
                    auto_off: this.comms.auto_off || true,
                    on_indefinitely: this.comms.on_indefinitely || false,
                    week_start: this.comms.week_start? this.comms.week_start : '',
                    week_end: this.comms.week_end ? this.comms.week_end : '',
                    weekend_start: this.comms.weekend_start ? this.comms.weekend_start : '',
                    weekend_end: this.comms.weekend_end ? this.comms.weekend_end : '',
                    message: this.comms.message || '',
                }),
            }
        },

        methods: {


            async fetchMessagingData(){

                console.log("Fetch messaging data");
                let response = await axios.get('/business/communication/sms-autoresponse/' + this.businessId)
                        .then(response => {
                            console.log("Logging response");
                            console.log(response.data);
                            this.form = response.data;
                        }).catch(error => {
                        console.log("Logging error response");
                            console.error(error.response);
                        });

                /*
                if (Array.isArray(response.data)) {
                    console.log(response.data);
                    this.form = response.data;
                }*/
            },

            saveMessaging(){

                console.log("Saving messaging");

                let params = '?auto_off=' + this.form.auto_off + "&on_indefinitely=" +
                             this.form.on_indefinitely + "&message=" + this.form.message +
                             '&week_start=' + this.form.week_start + '&week_end=' + this.form.week_end +
                             '&weekend_start=' + this.form.weekend_start + '&weekend_end=' + this.form.weekend_end;

                const response = axios.post('/business/communication/sms-autoresponse/' + this.businessId + params)
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

            businessId : Number,
            comms: {
                type: Object,
                default(){
                    return {};
                }
            }
        },

        watch: {
            message: function(val){
                if(val.length >= smsLength){
                   this.form.message = val.substring(0, smsLength);
                }
            },
            week_start: function(val){

                console.log("Start time val is " + val);

                //week_start = week_start.format('HH:mm');
                this.form.week_start = val;
            },
            week_end: function(val){

                console.log("End time val is " + val);

               // week_end = week_end.format('HH:mm');
                this.form.week_end = val;
            },
            weekend_start: function(val){
                    this.form.weekend_start = val;
            },
            weekend_end: function(val){
                this.form.weekend_end = val;
            },
        }
    }
</script>

<style scoped>

</style>