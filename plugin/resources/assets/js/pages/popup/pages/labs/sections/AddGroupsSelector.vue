<template>
	<div>
	    Add group by
	    <div>
	        <v-btn outlined @click="toggle(false)" :class="[grouping?'':'grp-btn-active']">Name</v-btn> 
	        <v-btn outlined @click="toggle(true)" :class="[grouping?'grp-btn-active':'']">Grouping</v-btn> 
	    </div>

	    <div>
	        <v-autocomplete v-if="grouping"
	            v-model="group"
	            :items="courseGroupings"
	            item-text="name"
	            item-value="id"
	            label="Select grouping"
	            @change="groupSelected"
	            return-object>
	        </v-autocomplete>
	        <v-autocomplete v-else
	            v-model="group"
	            :items="courseGroups"
	            item-text="name"
	            item-value="id"
	            label="Select group"
	            @change="groupSelected"
	            return-object>
	        </v-autocomplete>
	    </div>

	    <div>
	        <span class="multiselect__tag" v-for="g in groupsSorted" :key="g.id">
	            <span> {{ g.name }} </span>
	            <i class="multiselect__tag-icon" @mousedown.prevent="removeGroup(g)"></i>
	        </span>
	    </div>

	</div>
</template>

<script>

    export default {

        data() {
            return {
                group: {},
                grouping: false,
            }
        },

        props: {
            lab: {required: true},
            courseGroups: {required: true},
            courseGroupings: {required: true},
        },

        methods: {
            toggle(bool) {
                this.grouping = bool;
                this.group = {};
            },

            groupSelected() {
                var self = this;
                var addGroup = function(group) {
                    if (!self.lab.groups.find( (g) => { return g.id == group.id; } )) {
                        self.lab.groups.push(group);
                    }
                }

                if (this.grouping) {
                    this.group.groups.forEach( function (g) { addGroup(g); });
                } else {
                    addGroup(this.group);
                }
                this.group = {};
            },

            removeGroup(g) {
                let indx = this.lab.groups.indexOf(g);
                this.lab.groups.splice(indx, 1);
            },
        },

        computed: {
            groupsSorted: function() {
              return this.lab.groups.sort((a,b) => { return a.name.localeCompare(b.name); });
            },
        },
    }
</script>


<style>
    .grp-btn-active {
        color: green !important;
    }
</style>