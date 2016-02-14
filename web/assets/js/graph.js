
function GraphRenderer(viewPortId) {

    this.init(viewPortId);

}

GraphRenderer.prototype.init = function (viewPortId) {
    this.arbor = arbor.ParticleSystem(1000, 400, 1);
    this.arbor.parameters({gravity:false});
    this.arbor.renderer = Renderer(viewPortId) ;
};

GraphRenderer.prototype.render = function (graphData) {
    this.arbor.graft(graphData);
};