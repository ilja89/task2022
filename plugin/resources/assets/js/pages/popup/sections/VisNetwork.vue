<template>
    <div ref="visNetwork"></div>
</template>

<script>
import {Network} from "vis-network/peer/";
import {DataSet} from "vis-data/peer/";
import {NEUTRAL, INTERESTING, SUSPICIOUS, WARNING, DANGER} from '../../../helpers/PlagiarismColors';

export default {
    name: "VisNetwork",
    props: {
        nodes: {
            required: false,
            default: []
        },

        edges: {
            required: false,
            default: []
        }
    },

    computed: {
        nodesAndEdges() {
            return {
                nodes: this.nodes,
                edges: this.edges
            }
        }
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
            }
        }
    }
}
</script>

<style scoped>

</style>