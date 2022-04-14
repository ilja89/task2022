<template>
    <div>
        <div v-if="matchModal && getMatch()" >
            <div class="select" :class="[ 'medium' ]">
                <select
                    name="match"
                    v-model="selectedMatchId"
                >
                    <option
                        v-for="match in matchesList"
                        :value="match.id"
                    >
                      {{ match.uniid + ' - ' + match.percentage + '% - ' + match.other_uniid }}
                    </option>
                </select>
            </div>
            <plagiarism-match-modal :match="getMatch()"></plagiarism-match-modal>
        </div>

        <div ref="visNetwork"></div>
    </div>
</template>

<script>
import {Network} from "vis-network/peer/";
import {DataSet} from "vis-data/peer/";
import {NEUTRAL, INTERESTING, SUSPICIOUS, WARNING, DANGER} from '../../../helpers/PlagiarismColors';
import PlagiarismMatchModal from "../partials/PlagiarismMatchModal";
import PopupSelect from "../partials/PopupSelect";

export default {
    name: "VisNetwork",

    components: {PlagiarismMatchModal, PopupSelect},

    props: {
        nodes: { required: false, default: [] },
        edges: { required: false, default: [] },
        matches: { required: true }
    },

    computed: {
        nodesAndEdges() {
            return {
                nodes: this.nodes,
                edges: this.edges
            }
        },
    },

    watch: {
        nodesAndEdges(newVal, oldVal) {
            this.networkNodes.clear()
            this.networkEdges.clear()
            this.networkNodes.add(newVal.nodes)
            this.networkEdges.add(newVal.edges)
        }
    },

    mounted() {
        const container = document.getElementById("visNetwork");

        this.networkNodes.add(this.nodes)
        this.networkEdges.add(this.edges)
        const data = {
            nodes: this.networkNodes,
            edges: this.networkEdges,
        };

        const network = new Network(this.$refs.visNetwork, data, this.options);

        network.on('select', (data) => {
            if (data.edges.length > 0) {
                let matches = []
                this.matches.forEach(match => {
                    if (data.edges.includes(match.id)) {
                        this.selectedMatchId = match.id
                        matches.push(match)
                    }
                })
                this.matchesList = matches
                this.matchModal = true
            } else {
                this.matchModal = false
            }
        })

    },

    methods: {
        getMatch() {
            let matchById = null
            this.matches.forEach(match => {
                if (match.id === this.selectedMatchId) {
                    matchById = match
                }
            })
            return matchById
        }
    },

    data() {
        return {
            networkNodes: new DataSet(),
            networkEdges: new DataSet(),
            options: {
                height: "500px",
                edges: {
                    width: 2,
                },
                physics: {
                    stabilization: false,
                },
                groups: {
                    [NEUTRAL]: {
                        color: {
                            border: '#3E7DE2',
                            background: '#9FC2F7',
                            highlight: {background: '#8AC3FF', border: '#3E7DE2'},
                        },
                    },
                    [INTERESTING]: {
                        color: {
                            border: '#302CAB',
                            background: '#6B72F4',
                            highlight: {background: '#6970F4', border: '#302CAB'},
                        },
                    },
                    [SUSPICIOUS]: {
                        color: {
                            border: '#F3A83B',
                            background: '#F8F652',
                            highlight: {background: '#DAD84C', border: '#F3A83B'},
                        },
                    },
                    [WARNING]: {
                        color: {
                            border: '#BA812C',
                            background: '#F4AB3E',
                            highlight: {background: '#FAAE41', border: '#BA812C'},
                        },
                    },
                    [DANGER]: {
                        color: {
                            border: '#E43428',
                            background: '#EC8584',
                            highlight: {background: '#F54137', border: '#E43428'},
                        },
                    },
                },
            },
            matchModal: false,
            selectedMatchId: null,
            matchesList: [],
        }
    }
}
</script>

<style scoped>

</style>