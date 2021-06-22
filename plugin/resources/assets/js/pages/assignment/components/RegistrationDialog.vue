<template>
    <v-dialog v-model="dialog" fullscreen hide-overlay style="position: relative; z-index: 2000"
    transition="dialog-bottom-transition">
        <!-- Button below submissions -->
        <template v-slot:activator="{ on, attrs }">
            <v-btn v-bind="attrs" v-on="on" class="mt-4 ml-4" color="primary" dense outlined @click="dialog=true">
                <!-- TODO: translate -->
                New registration
            </v-btn>
        </template>

        <v-card>
            <!---------------------------->
            <!-- Main content -->
            <!---------------------------->
            <v-card class="mx-auto">
                <v-sheet class="pa-4 primary lighten-2">
                    <v-text-field v-model="search" clear-icon="mdi-close-circle-outline" clearable dark flat
                    hide-details label="Search Company Directory" solo-inverted/>
                </v-sheet>

                <v-card-text>
                    <v-row>
                        <v-col>
                            <!---------------------------->
                            <!-- Charons treeview -->
                            <!---------------------------->
                            <v-treeview
                                v-model="submissionSelection"
                                :active.sync="active"
                                :items="items"
                                :search="search"
                                activatable
                                color="indigo"
                                dense
                                expand-icon="mdi-chevron-down"
                                indeterminate-icon="mdi-shield-plus"
                                off-icon="mdi-shield-outline"
                                on-icon="mdi-shield"
                                open-on-click
                                return-object
                                selectable
                                transition
                            >
                                <template v-slot:prepend="{ item }">
                                    <v-icon v-if="item.children" v-text="`mdi-${item.id === 1 ? 'git' : 'folder-network'}`"/>
                                </template>
                            </v-treeview>
                        </v-col>

                        <v-divider vertical></v-divider>

                        {{ active }}

                        <!---------------------------->
                        <!-- Selected nodes info -->
                        <!---------------------------->
                        <v-col class="pa-6" cols="6">
                            <template v-if="!submissionSelection.length">
                                No nodes selected.
                            </template>

                            <template v-else>
                                TODO some info about submission:
                                <div v-for="node in submissionSelection" :key="node.id">
                                    {{ node.name }}
                                </div>
                            </template>
                        </v-col>
                    </v-row>

                    <v-divider></v-divider>

                    <v-row class="ma-5 d-flex align-center">

                        <!---------------------------->
                        <!-- Selected charons list -->
                        <!---------------------------->
                        <v-card class="pa-md-4 d-flex flex-column" style="width: fit-content">
                            <p class="text-sm-h5 text-center">Selected charons</p>

                            <v-divider style="margin-bottom: 1rem"></v-divider>

                            <v-card-text class="text-center text-lg-button" v-if="selectedCharons.length === 0">
                                <v-icon>mdi-alert-circle-outline</v-icon>
                                No charons selected
                            </v-card-text>

                            <div class="scrollable" style="max-height: 150px">
                                <v-card class="registered-charon" v-for="charon in selectedCharons">
                                    <v-card class="d-flex justify-space-between" style="border-bottom-left-radius: 0; border-bottom-right-radius: 0" :color="charon.event.color">
                                        <v-card-text class="white--text" style="padding: 0 0.5rem">{{charon.event.name}}</v-card-text>
                                        <v-card-text class="white--text text-right" style="padding: 0 0.5rem">12.05.2020</v-card-text>
                                    </v-card>
                                    <div style="padding: 0.4rem" class="d-flex justify-space-between">
                                        <span>
                                            <v-icon>mdi-git</v-icon>
                                            {{charon.name}}
                                        </span>
                                        <v-btn @click="deselectCharon(charon)" outlined class="white--text" color="red" small style="margin-left: 0.5rem">
                                            Cancel
                                        </v-btn>
                                    </div>
                                </v-card>
                            </div>

                            <v-card-text v-if="selectedCharons.length > 0" class="text-center">Session time left: 00:00</v-card-text>

                            <v-divider class="ma-4"></v-divider>

                            <div class="d-flex justify-center">
                                <v-badge v-if="selectedCharons.length > 0" style="width: fit-content" bordered overlap :content="selectedCharons.length" v-resize="2">
                                    <v-btn @click="selectedCharons = []" class="ma-2 blue white--text">Deselect all</v-btn>
                                </v-badge>

                                <v-btn disabled v-else class="ma-2 blue white--text">Deselect all</v-btn>

                                <v-dialog
                                    v-model="bookingModal"
                                    v-if="!dontShowModal"
                                    persistent
                                    width="300"
                                >
                                    <template v-slot:activator="{on, attrs}">
                                        <v-btn
                                            class="ma-2 green white--text"
                                            :disabled="selectedCharons.length === 0"
                                            v-bind="attrs"
                                            v-on="on"
                                        >
                                            Book all
                                        </v-btn>
                                    </template>

                                    <v-card>
                                        <v-card-title>
                                            Caution
                                        </v-card-title>

                                        <v-card-text class="text-lg-body-1">
                                            Are you <b>sure?</b> Booked charons cannot be un-booked.
                                        </v-card-text>

                                        <div style="margin-left: 1.5rem">
                                            <v-checkbox v-model="dontShowModalBox" style="margin: 0" label="Do not show again"></v-checkbox>
                                        </div>

                                        <v-card-actions>
                                            <v-spacer></v-spacer>
                                            <v-btn color="green" text
                                                @click="() => {
                                                    addBookings();
                                                    bookingModal = false;
                                                }"
                                            >
                                                Proceed
                                            </v-btn>
                                            <v-btn color="red" text
                                                @click="bookingModal = false"
                                            >
                                                Cancel
                                            </v-btn>
                                        </v-card-actions>
                                    </v-card>
                                </v-dialog>

                                <v-btn color="green" v-else :disabled="selectedCharons.length === 0" @click="addBookings" class="ma-2 blue white--text">Book all</v-btn>
                            </div>
                        </v-card>


                        <div style="margin: 0 2rem 0 1rem">
                            <i class="arrow right"/>
                            <i class="arrow right"/>
                            <i class="arrow right"/>
                        </div>

                        <!---------------------------->
                        <!-- Booked charons list -->
                        <!---------------------------->
                        <v-card class="pa-md-4 d-flex flex-column" style="width: fit-content">
                            <p class="text-sm-h5 text-center">Booked charons</p>

                            <v-divider style="margin-bottom: 1rem"></v-divider>

                            <v-card-text v-if="bookedCharons.length === 0" class="text-center text-lg-button">
                                <v-icon>mdi-alert-circle-outline</v-icon>
                                Saved bookings not found
                            </v-card-text>

                            <div class="booking" v-for="bookedCharon in bookedCharons">
                                <div class="booking-title">
                                    <div style="display: flex">
                                        <span style="margin-right: 0.4rem">Event:</span>
                                        <v-card :color="bookedCharon.event.color">
                                            <v-card-text class="white--text" style="padding: 0 0.5rem">{{bookedCharon.event.name}}</v-card-text>
                                        </v-card>
                                    </div>
                                    <div class="booking-date">
                                        12.05.2020 12:00
                                    </div>
                                </div>
                                <v-divider></v-divider>
                                <div class="booking-body">
                                    <div class="booking-charon" v-for="charon in bookedCharon.charons">
                                        <v-icon>mdi-git</v-icon>
                                        {{charon.name}}
                                    </div>
                                </div>
                            </div>
                            <v-divider class="ma-4"></v-divider>
                            <v-card-text>Note: Saved bookings are not supposed to be canceled</v-card-text>
                        </v-card>

                    </v-row>

                    <v-divider></v-divider>

                    <v-row class="fill-height">
                        <v-col>
                            <!---------------------------->
                            <!-- Calendar container -->
                            <!---------------------------->
                            <v-sheet height="64">
                                <v-toolbar flat>
                                    <v-btn class="mr-4" color="grey darken-2" outlined @click="setToday">
                                        Today
                                    </v-btn>

                                    <v-btn color="grey darken-2" fab small text @click="prev">
                                        <v-icon small>mdi-chevron-left</v-icon>
                                    </v-btn>

                                    <v-btn color="grey darken-2" fab small text @click="next">
                                        <v-icon small>mdi-chevron-right</v-icon>
                                    </v-btn>

                                    <v-toolbar-title v-if="$refs.calendar">
                                        {{ $refs.calendar.title }}
                                    </v-toolbar-title>

                                    <v-spacer></v-spacer>

                                    <v-menu bottom right>
                                        <template v-slot:activator="{ on, attrs }">
                                            <v-btn v-bind="attrs" v-on="on" color="grey darken-2" outlined>
                                                <span>{{ typeToLabel[type] }}</span>
                                                <v-icon right>mdi-menu-down</v-icon>
                                            </v-btn>
                                        </template>

                                        <v-list>
                                            <v-list-item @click="type = 'day'">
                                                <v-list-item-title>Day</v-list-item-title>
                                            </v-list-item>

                                            <v-list-item @click="type = 'week'">
                                                <v-list-item-title>Week</v-list-item-title>
                                            </v-list-item>

                                            <v-list-item @click="type = 'month'">
                                                <v-list-item-title>Month</v-list-item-title>
                                            </v-list-item>

                                            <v-list-item @click="type = '4day'">
                                                <v-list-item-title>4 days</v-list-item-title>
                                            </v-list-item>
                                        </v-list>
                                    </v-menu>
                                </v-toolbar>
                            </v-sheet>

                                <!---------------------------->
                                <!-- Calendar -->
                                <!---------------------------->
                            <v-sheet height="600">
                                <v-calendar
                                    ref="calendar"
                                    v-model="focus"
                                    color="primary"
                                    :events="events"
                                    :event-color="getEventColor"
                                    :type="type"
                                    @click:event="showEvent"
                                    @click:more="viewDay"
                                    @click:date="viewDay"
                                    @change="updateRange"
                                >
                                    <template v-slot:event="{event}">
                                        <div class="pl-2" style="display: flex; align-items: center">
                                            <v-btn
                                                v-if="event.charons.length > 0"
                                                color="light-green"
                                                class="slot-charons white--text text-truncate d-inline-block"
                                                style="margin-right: 0.5rem; font-size: 11px; height: fit-content; padding: 0.1rem; max-width: 30px"
                                                x-small
                                            >
                                            {{event.charons[0].shortName}}
                                            </v-btn>

                                            <v-btn v-if="event.charons.length > 1" color="light-green" style="margin-right: 0.5rem; font-size: 11px; height: fit-content; padding: 0.1rem;" x-small class="slot-charons white--text">
                                                {{event.charons.length - 1}} more
                                            </v-btn>

                                            {{ event.name }}
                                        </div>
                                    </template>
                                </v-calendar>

                                <!---------------------------->
                                <!-- Calendar floating menu -->
                                <!---------------------------->
                                <v-menu v-model="selectedOpen" :activator="selectedElement"
                                    :close-on-content-click="false" offset-x>

                                    <v-card color="grey lighten-4" flat min-width="350px">
                                        <v-toolbar :color="this.selectedEvent.color" dark>
                                            <v-toolbar-title v-html="selectedEvent.name"></v-toolbar-title>
                                        </v-toolbar>

                                        <v-container class="pa-2">
                                            <v-card class="charon-event-card" v-for="charon in selectedEvent.charons">
                                                <div :class="getCharonEventClasses(charon)" style="display: flex; align-items: center; padding-right: 1rem">
                                                    <v-card-text>
                                                        {{charon.name}}
                                                    </v-card-text>
                                                    <v-btn @click="() => {
                                                        if (isCharonSelected(charon)) {
                                                            selectedCharons = selectedCharons.filter((obj) => {
                                                                return obj.name !== charon.name && obj.event !== charon.event
                                                            })
                                                        } else {
                                                            selectedCharons.push({
                                                                name: charon.name,
                                                                event: selectedEvent
                                                            })
                                                        }
                                                    }">
                                                        {{isCharonSelected(charon) ? 'Cancel' : 'Select'}}
                                                    </v-btn>
                                                </div>
                                            </v-card>
                                        </v-container>

                                        <v-card-actions>
                                            <v-btn
                                                v-if="selectedEvent.charons && selectedEvent.charons.length > 0"
                                                color="primary"
                                                text
                                                :disabled="getEventSelectedCharons(selectedEvent).length === selectedEvent.charons.length"
                                                @click="addAllCurrentCharons"
                                            >
                                                Select all
                                            </v-btn>

                                            <v-btn
                                                v-if="selectedEvent.charons && selectedEvent.charons.length > 0"
                                                :disabled="getEventSelectedCharons(selectedEvent).length === 0"
                                                color="primary"
                                                text
                                                @click="getEventSelectedCharons(selectedEvent).forEach(obj => deselectCharon(obj))"
                                            >
                                                Deselect all
                                            </v-btn>

                                            <v-btn color="secondary" text @click="selectedOpen = false">
                                                Close
                                            </v-btn>
                                        </v-card-actions>
                                    </v-card>
                                </v-menu>

                            </v-sheet>
                        </v-col>
                    </v-row>
                </v-card-text>
            </v-card>
        </v-card>
    </v-dialog>
</template>

<script>
import {getSubmissionWeightedScore} from "../helpers/submission";

export default {
    name: "registration-dialog",

    data() {
        return {
            dialog: false,
            notifications: false,
            sound: true,
            widgets: false,
            rangeStart: Date.now(),
            rangeEnd: Date.now(),

            // tree view
            active: [],
            submissionSelection: [],
            search: null,
            // Items in node list (tree-view)
            items: [],
            labs: [],
            // Modal which pops up when pressing "Book all"
            bookingModal: null,
            // Currently selected charons
            selectedCharons: [],
            // Booked charons
            bookedCharons: [],
            // Model for checkbox
            dontShowModalBox: false,
            // Determines whether if bookingModal should appear.
            // Is taken from localStorage.
            dontShowModal: false,

            // Calendar stuff
            focus: '',
            type: 'month',
            typeToLabel: {
                month: 'Month',
                week: 'Week',
                day: 'Day',
                '4day': '4 Days',
            },
            selectedEvent: {},
            selectedElement: null,
            selectedOpen: false,
            events: [],

            // generation
            colors: ['blue', 'indigo', 'deep-purple', 'cyan', 'green', 'orange', 'grey darken-1'],
            names: ['Meeting', 'Holiday', 'PTO', 'Travel', 'Event', 'Birthday', 'Conference', 'Party'],
        }
    },

    created() {
        this.items = [
            {
                id: 2,
                name: 'EX12',
                children: [
                    {
                        id: 201,
                        git_timestamp: "2020-12-05 18:23:59",
                        charon_id: 2,
                        test_suites: [
                            {
                                weight: 1,
                                grade: 100
                            },
                        ],
                        results: [
                            {
                                id: 752,
                                submission_id: 225,
                                calculated_result: "1.00",
                                grade_type_code: 1
                            }, {
                                id: 751,
                                submission_id: 225,
                                calculated_result: "1.00",
                                grade_type_code: 101
                            }, {
                                id: 753,
                                submission_id: 225,
                                calculated_result: "0.00",
                                grade_type_code: 1001
                            }
                        ],
                        result: 100,
                        name: 'EX12 - 2020-12-05 18:23:59 - 100%',
                    },
                    {
                        id: 202,
                        git_timestamp: "2020-12-06 18:23:59",
                        charon_id: 2,
                        test_suites: [
                            {
                                weight: 1,
                                grade: 90
                            },
                        ],
                        results: [
                            {
                                id: 752,
                                submission_id: 225,
                                calculated_result: "1.00",
                                grade_type_code: 1
                            }, {
                                id: 751,
                                submission_id: 225,
                                calculated_result: "1.00",
                                grade_type_code: 101
                            }, {
                                id: 753,
                                submission_id: 225,
                                calculated_result: "0.00",
                                grade_type_code: 1001
                            }
                        ],
                        result: 90,
                        name: 'EX12 - 2020-12-06 18:23:59 - 90%',
                    },
                    {
                        id: 203,
                        git_timestamp: "2020-12-07 18:23:59",
                        charon_id: 2,
                        test_suites: [
                            {
                                weight: 1,
                                grade: 80
                            },
                        ],
                        results: [
                            {
                                id: 752,
                                submission_id: 225,
                                calculated_result: "1.00",
                                grade_type_code: 1
                            }, {
                                id: 751,
                                submission_id: 225,
                                calculated_result: "1.00",
                                grade_type_code: 101
                            }, {
                                id: 753,
                                submission_id: 225,
                                calculated_result: "0.00",
                                grade_type_code: 1001
                            }
                        ],
                        result: 80,
                        name: 'EX12 - 2020-12-07 18:23:59 - 80%',
                    },
                    {
                        id: 204,
                        git_timestamp: "2020-12-08 18:23:59",
                        charon_id: 2,
                        test_suites: [
                            {
                                weight: 1,
                                grade: 40
                            },
                        ],
                        results: [
                            {
                                id: 752,
                                submission_id: 225,
                                calculated_result: "1.00",
                                grade_type_code: 1
                            }, {
                                id: 751,
                                submission_id: 225,
                                calculated_result: "1.00",
                                grade_type_code: 101
                            }, {
                                id: 753,
                                submission_id: 225,
                                calculated_result: "0.00",
                                grade_type_code: 1001
                            }
                        ],
                        result: 40,
                        name: 'EX12 - 2020-12-08 18:23:59 - 40%',
                    },
                    {
                        id: 205,
                        git_timestamp: "2020-12-09 18:23:59",
                        charon_id: 2,
                        test_suites: [
                            {
                                weight: 1,
                                grade: 50
                            },
                            {
                                weight: 1,
                                grade: 70
                            },
                        ],
                        results: [
                            {
                                id: 752,
                                submission_id: 225,
                                calculated_result: "1.00",
                                grade_type_code: 1
                            }, {
                                id: 751,
                                submission_id: 225,
                                calculated_result: "1.00",
                                grade_type_code: 101
                            }, {
                                id: 753,
                                submission_id: 225,
                                calculated_result: "0.00",
                                grade_type_code: 1001
                            }
                        ],
                        result: 60,
                        name: 'EX12 - 2020-12-09 18:23:59 - 60%',
                    },
                ],
            },
            {
                id: 3,
                name: 'EX13',
                children: [
                    {
                        id: 301,
                        git_timestamp: "2021-1-05 18:23:59",
                        charon_id: 3,
                        test_suites: [
                            {
                                weight: 1,
                                grade: 30
                            },
                            {
                                weight: 1,
                                grade: 70
                            },
                        ],
                        results: [
                            {
                                id: 752,
                                submission_id: 225,
                                calculated_result: "1.00",
                                grade_type_code: 1
                            }, {
                                id: 751,
                                submission_id: 225,
                                calculated_result: "1.00",
                                grade_type_code: 101
                            }, {
                                id: 753,
                                submission_id: 225,
                                calculated_result: "0.00",
                                grade_type_code: 1001
                            }
                        ],
                        result: 50,
                        name: 'EX13 - 2021-1-05 18:23:59 - 50%',
                    },
                    {
                        id: 302,
                        git_timestamp: "2021-1-06 18:23:59",
                        charon_id: 3,
                        test_suites: [
                            {
                                weight: 1,
                                grade: 90
                            },
                            {
                                weight: 1,
                                grade: 70
                            },
                        ],
                        results: [
                            {
                                id: 752,
                                submission_id: 225,
                                calculated_result: "1.00",
                                grade_type_code: 1
                            }, {
                                id: 751,
                                submission_id: 225,
                                calculated_result: "1.00",
                                grade_type_code: 101
                            }, {
                                id: 753,
                                submission_id: 225,
                                calculated_result: "0.00",
                                grade_type_code: 1001
                            }
                        ],
                        result: 80,
                        name: 'EX13 - 2021-1-06 18:23:59 - 80%',
                    },
                ],
            },
            {
                id: 4,
                name: 'EX14',
                children: [
                    {
                        id: 401,
                        git_timestamp: "2021-2-05 18:23:59",
                        charon_id: 4,
                        test_suites: [
                            {
                                weight: 1,
                                grade: 10
                            },
                            {
                                weight: 1,
                                grade: 10
                            },
                            {
                                weight: 1,
                                grade: 10
                            },
                        ],
                        results: [
                            {
                                id: 752,
                                submission_id: 225,
                                calculated_result: "1.00",
                                grade_type_code: 1
                            }, {
                                id: 751,
                                submission_id: 225,
                                calculated_result: "1.00",
                                grade_type_code: 101
                            }, {
                                id: 753,
                                submission_id: 225,
                                calculated_result: "0.00",
                                grade_type_code: 1001
                            }
                        ],
                        result: 10,
                        name: 'EX13 - 2021-2-05 18:23:59 - 10%',
                    },
                    {
                        id: 402,
                        git_timestamp: "2021-2-06 18:23:59",
                        charon_id: 4,
                        test_suites: [
                            {
                                weight: 3,
                                grade: 10
                            },
                            {
                                weight: 4,
                                grade: 30
                            },
                            {
                                weight: 1,
                                grade: 10
                            },
                        ],
                        results: [
                            {
                                id: 752,
                                submission_id: 225,
                                calculated_result: "1.00",
                                grade_type_code: 1
                            }, {
                                id: 751,
                                submission_id: 225,
                                calculated_result: "0.00",
                                grade_type_code: 101
                            }, {
                                id: 753,
                                submission_id: 225,
                                calculated_result: "0.00",
                                grade_type_code: 1001
                            }
                        ],
                        result: 0,
                        name: 'EX14 - 2021-2-06 18:23:59 - 0%',
                    },
                    {
                        id: 403,
                        git_timestamp: "2021-2-07 18:23:59",
                        charon_id: 4,
                        test_suites: [
                            {
                                weight: 3,
                                grade: 10
                            },
                            {
                                weight: 4,
                                grade: 10
                            },
                            {
                                weight: 1,
                                grade: 10
                            },
                        ],
                        results: [
                            {
                                id: 752,
                                submission_id: 225,
                                calculated_result: "1.00",
                                grade_type_code: 1
                            }, {
                                id: 751,
                                submission_id: 225,
                                calculated_result: "1.00",
                                grade_type_code: 101
                            }, {
                                id: 753,
                                submission_id: 225,
                                calculated_result: "0.00",
                                grade_type_code: 1001
                            }
                        ],
                        result: 10,
                        name: 'EX14 - 2021-2-07 18:23:59 - 10%',
                    },
                ],
            },
        ];
        this.labs = [
            {
                id: 1,
                name: this.names[this.rnd(0, this.names.length - 1)],
                start: new Date(`2021-06-20T10:00:00`),
                end: new Date(`2021-06-20T10:30:00`),
                color: this.colors[this.rnd(0, this.colors.length - 1)],
                timed: true,
                charonsAvailable: 1,
                charonIds: [2]
            },
            {
                id: 1,
                name: this.names[this.rnd(0, this.names.length - 1)],
                start: new Date(`2021-06-20T11:00:00`),
                end: new Date(`2021-06-20T11:30:00`),
                color: this.colors[this.rnd(0, this.colors.length - 1)],
                timed: true,
                charonsAvailable: 1,
                charonIds: [2]
            },
            {
                id: 2,
                name: this.names[this.rnd(0, this.names.length - 1)],
                start: new Date(`2021-06-20T11:00:00`),
                end: new Date(`2021-06-20T11:30:00`),
                color: this.colors[this.rnd(0, this.colors.length - 1)],
                timed: true,
                charonsAvailable: 3,
                charonIds: [2, 3, 4]
            }
        ];

        this.labs.forEach(lab => {
            const charonIds = lab.charonIds;
            lab.charons = this.items
                .filter(charon => {
                    return charonIds.includes(charon.id);
                })
                .map(charon => {
                    return {
                        id: charon.id,
                        name: charon.name,
                        shortName: charon.name
                    }
                });
        });
    },

    computed: {},

    watch: {
        submissionSelection: function (updatedSubmissionSelection) {
            let maxSubs = {}
            let sub;

            for (sub of updatedSubmissionSelection) {
                if (!maxSubs[sub.charon_id] || maxSubs[sub.charon_id].result < sub.result) {
                    if (sub.result >= 50) { //TODO: check if style OK and result over thresh hold and is not defended already
                        maxSubs[sub.charon_id] = sub
                    }
                }
            }

            const newSubmissionSelection = Object.keys(maxSubs).map(key => maxSubs[key]);

            if (newSubmissionSelection.length + 1 === updatedSubmissionSelection.length) {
                VueEvent.$emit('show-notification', `TODO: reason why choice is not possible or suboptimal`, 'warning')
            }

            for (let i = 0; i < this.submissionSelection.length; ++i) {
                if (!newSubmissionSelection[i] || !this.submissionSelection[i] || newSubmissionSelection[i].id !== this.submissionSelection[i].id) {
                    this.submissionSelection = newSubmissionSelection;
                    break;
                }
            }

            this.updateEvents();
        }
    },

    mounted() {
        this.dontShowModal = localStorage.getItem("dontShowBookModal") === 'true';
        this.$refs.calendar.checkChange()
    },

    methods: {
        // Return true if charon is currently selected
        isCharonSelected(charon) {
            return this.selectedCharons.find(obj => obj.name === charon.name) !== undefined;
        },

        // Return true if charon is booked
        isCharonBooked(charon) {
            return this.bookedCharons.find(obj => obj.charons.find((chObj) => chObj.name === charon.name) !== undefined) !== undefined;
        },

        // Deselect a charon which is in selectedCharons list
        deselectCharon(charon) {
            this.selectedCharons = this.selectedCharons.filter(obj => obj.name !== charon.name && obj.event !== charon.event);
        },

        // Return a charons list which are connected to specific event
        getEventSelectedCharons(event) {
            const result = []
            for (let charon of event.charons) {
                if(this.selectedCharons.find(obj => obj.name === charon.name) !== undefined) {
                    result.push(charon);
                }
            }
            return result;
        },

        // Add all currently selected (in calendar) charons
        addAllCurrentCharons() {
            //@ts-ignore
            for (let evtCharon of this.selectedEvent.charons) {
                if (this.selectedCharons.find(obj => obj.name === evtCharon.name)) {
                    continue;
                }
                this.selectedCharons.push({
                    name: evtCharon.name,
                    event: this.selectedEvent
                })
            }
        },

        // Get CSS classes for event
        getCharonEventClasses(charon) {
            if (this.isCharonSelected(charon)) {
                return 'green-outlined'
            } else if (this.isCharonBooked(charon)) {
                return 'green white--text'
            } else {
                return 'white'
            }
        },

        // Book all selected charons
        addBookings() {
            for (let charon of this.selectedCharons) {
                this.bookCharon(charon);
            }
            this.selectedCharons = [];
            localStorage.setItem("dontShowBookModal", this.dontShowModalBox ? 'true' : 'false');
            this.dontShowModal = this.dontShowModalBox;
        },

        // Book specific charon
        bookCharon(charon) {
            const bookedCharonEventPair = this.bookedCharons.find((obj) => obj.event === charon.event)
            if (bookedCharonEventPair) {
                bookedCharonEventPair.charons.push({name: charon.name});
            } else {
                this.bookedCharons.push({
                    event: charon.event,
                    charons: [
                        {
                            name: charon.name
                        }
                    ]
                });
            }
        },

        registerSelected() {
            VueEvent.$emit('show-notification', this.submissionSelection + 'was registered for event: ' + this.selectedEvent , 'info')
        },

        viewDay({date}) {
            this.focus = date
            this.type = 'day'
        },

        getEventColor(event) {
            return event.color
        },

        setToday() {
            this.focus = ''
        },

        prev() {
            this.$refs.calendar.prev()
        },

        next() {
            this.$refs.calendar.next()
        },

        showEvent({nativeEvent, event}) {
            const open = () => {
                this.selectedEvent = event
                this.selectedElement = nativeEvent.target
                setTimeout(() => {
                    this.selectedOpen = true
                }, 10)
            }

            if (this.selectedOpen) {
                this.selectedOpen = false
                setTimeout(open, 10)
            } else {
                open()
            }

            nativeEvent.stopPropagation()
        },

        updateRange({start, end}) {
            this.rangeStart = new Date(`${start.date}T00:00:00`);
            this.rangeEnd = new Date(`${end.date}T23:59:59`);
            this.updateEvents();
        },

        updateEvents() {
            const charonIds = this.submissionSelection.map(selected => {
                return selected.charon_id;
            });

            if (charonIds.length === 0) {
                this.events = [];
                return;
            }

            const events = [];

            for (const lab of this.labs) {
                if (lab.start < this.rangeStart || lab.end > this.rangeEnd) {
                    continue;
                }
                if (charonIds.some(a => lab.charonIds.some(b => a === b))) {
                    events.push(lab);
                }
            }

            this.events = events
        },

        rnd(a, b) {
            return Math.floor((b - a + 1) * Math.random()) + a
        },
    },
}
</script>

<style scoped lang="scss">
    .charon-event-card + .charon-event-card {
        margin-top: 0.5rem;
    }

    .green-outlined {
        border: 2px solid #56b019;
    }

    .charon-day {
        background: #56b019;
        width: fit-content;
        padding: 0 0.5rem;
        border-radius: 20px;
        text-align: center;
    }

    .booking + .booking {
        margin-top: 0.5rem;
    }

    .booking {
        border-radius: 3px;
        border: 1px solid rgba(#000, 0.15);
        box-shadow: 0 2px 3px 0 rgba(#000, 0.2);
        padding: 0.4rem;

        .booking-title {
            font-size: 16px;
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.3rem;

            .booking-date {
                padding: 0 0.4rem;
                display: flex;
                align-items: center;
                font-size: 13px;
            }

            .booking-event {
                font-size: 18px;
                background: #56b019;
                padding: 0 0.4rem;
                color: #fff;
                margin-left: 0.4rem;
                border-radius: 5px;
            }
        }

        .booking-charon {
            border: 1px solid rgba(#000, 0.1);
            margin: 0.2rem 0;
            padding: 0 1rem;
            text-align: center;
        }
    }

    .scrollable {
        overflow: auto;
        padding: 0.5rem;
    }

    .arrow {
        border: solid rgba(black, 0.3);
        border-width: 0 3px 3px 0;
        display: inline-block;
        width: 40px;
        height: 40px;
        padding: 3px;
        transform: rotate(-45deg);
    }

    .registered-charon + .registered-charon {
        margin-top: 0.4rem;
    }
</style>
