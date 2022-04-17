<template>
    <div id="app">
        <div class="track-container">
            <div>
                <span class="range-value min">{{ minValue }}</span>
                <span class="range-value text">{{ text }}</span>
                <span class="range-value max">{{ maxValue }}</span>
            </div>
            <div class="track" ref="_vpcTrack"></div>
            <div class="track-highlight" ref="trackHighlight"></div>
            <button class="track-btn track1" ref="track1"></button>
            <button class="track-btn track2" ref="track2"></button>
        </div>
    </div>
</template>

<script>

export default {

    props: {
        text: {required: false, default: '', type: String},
        min: {required: false, default: 0, type: Number},
        max: {required: false, default: 100, type: Number},
        step: {required: false, default: 1, type: Number},
    },

    data() {
        return {
            totalSteps: 0,
            percentPerStep: 1,
            trackWidth: null,
            isDragging: false,
            pos: {
                curTrack: null
            },
            minValue: this.min,
            maxValue: this.max,
        }
    },

    methods: {
        moveTrack(track, ev) {

            let percentInPx = this.getPercentInPx();

            let trackX = Math.round(this.$refs._vpcTrack.getBoundingClientRect().left);
            let clientX = ev.clientX;
            let moveDiff = clientX-trackX;

            let moveInPct = moveDiff / percentInPx

            if (moveInPct<1 || moveInPct>100) return;
            let value = ( Math.round(moveInPct / this.percentPerStep) * this.step ) + this.min;
            if (track==='track1') {
                if(value >= (this.maxValue - this.step)) return;
                this.minValue = value;
            }

            if (track==='track2') {
                if(value <= (this.minValue + this.step)) return;
                this.maxValue = value;
            }

            this.$refs[track].style.left = moveInPct + '%';
            this.setTrackHightlight()
        },

        mousedown(ev, track){
            if(this.isDragging) return;
            this.isDragging = true;
            this.pos.curTrack = track;
        },

        touchstart(ev, track){
            this.mousedown(ev, track)
        },

        mouseup() {
            if(!this.isDragging) return;
            this.isDragging = false
        },

        touchend() {
            this.mouseup()
        },

        mousemove(ev, track) {
            if(!this.isDragging) return;
            this.moveTrack(track, ev)
        },

        mouseMoveEnd() {
            this.$emit('minMaxChanged', this.minValue, this.maxValue)
        },

        touchmove(ev, track) {
            this.mousemove(ev.changedTouches[0], track)
        },

        valueToPercent(value) {
            return ((value - this.min) / this.step) * this.percentPerStep
        },

        setTrackHightlight() {
            this.$refs.trackHighlight.style.left = this.valueToPercent(this.minValue) + '%'
            this.$refs.trackHighlight.style.width = (this.valueToPercent(this.maxValue) - this.valueToPercent(this.minValue)) + '%'
        },

        getPercentInPx() {
            let trackWidth = this.$refs._vpcTrack.offsetWidth;
            let oneStepInPx = trackWidth / this.totalSteps;
            let percentInPx = oneStepInPx / this.percentPerStep;

            return percentInPx;
        },

        async setClickMove(ev) {
            let track1Left = this.$refs.track1.getBoundingClientRect().left;
            let track2Left = this.$refs.track2.getBoundingClientRect().left;
            if (ev.clientX < track1Left) {
                this.moveTrack('track1', ev)
            } else if ((ev.clientX - track1Left) < (track2Left - ev.clientX)) {
                this.moveTrack('track1', ev)
            } else {
                this.moveTrack('track2', ev)
            }
            this.$emit('minMaxChanged', this.minValue, this.maxValue)
        }
    },

    mounted() {
        // calc per step value
        this.totalSteps = (this.max - this.min) / this.step;

        // percent the track button to be moved on each step
        this.percentPerStep = 100 / this.totalSteps;

        // set track1 initilal
        document.querySelector('.track1').style.left = this.valueToPercent(this.minValue) + '%'
        // track2 initial position
        document.querySelector('.track2').style.left = this.valueToPercent(this.maxValue) + '%'
        // set initila track highlight
        this.setTrackHightlight()

        var self = this;

        ['mouseup', 'mousemove'].forEach( type => {
            document.body.addEventListener(type, (ev) => {
                if(self.isDragging && self.pos.curTrack){
                    self[type](ev, self.pos.curTrack)
                }
            })
        });

        ['mousedown', 'mouseup', 'mousemove', 'touchstart', 'touchmove', 'touchend'].forEach( type => {
            document.querySelector('.track1').addEventListener(type, (ev) => {
                ev.stopPropagation();
                self[type](ev, 'track1')
            })

            document.querySelector('.track2').addEventListener(type, (ev) => {
                ev.stopPropagation();
                self[type](ev, 'track2')
            })
        });

        // on track click
        // determine direction based on click proximity
        // determine percent to move based on track.clientX - click.clientX
        document.querySelector('.track').addEventListener('click', function(ev) {
            ev.stopPropagation();
            self.setClickMove(ev)
        })

        document.querySelector('.track-highlight').addEventListener('click', function(ev) {
            ev.stopPropagation();
            self.setClickMove(ev)
        })
    }
}
</script>

<style lang="scss">

template {
    display: flex;
    width: 90%;
    height: 90%;
    align-items: center;
    justify-content: center;
    flex-direction:column;
}

.range-value{
    position: absolute;
    height: 0.3rem;
    top: -1.9rem;
}
.range-value.min{
    font-size: 12px;
    left: 0;
}

.range-value.text{
    font-size: 12px;
    display:inline-block;
    text-align: center;
  width: 100%;
}

.range-value.max{
    font-size: 12px;
    right: 0;
}
.track-container{
    width: 100%;
    position: relative;
    cursor: pointer;
}

.track,
.track-highlight {
    display: block;
    position: absolute;
    width: 100%;
    height: 0.3rem;
}

.track{
    background-color: #ddd;
    margin-left: -1rem;
}

.track-highlight{
    background-color: #2195f2;
    margin-left: -1rem;
}

.track-btn{
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    outline: none;
    cursor: pointer;
    display: block;
    position: absolute;
    z-index: 2;
    width: 0.8rem;
    height: 0.8rem;
    top: calc(-50% - 0.25rem);
    margin-left: -1rem;
    border: none;
    background-color: #1666a2;
    -ms-touch-action: pan-x;
    touch-action: pan-x;
    transition: transform .3s ease-out,box-shadow .3s ease-out,background-color .3s ease,-webkit-transform .3s ease-out;
}

</style>
