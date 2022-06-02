@extends('layouts.app')

@section('page-title') {{ __('general.Taxonomy') }} {{ __('beep.visual') }}
@endsection

@section('content')

    @component('components/box')
        @slot('title')
            {{ __('crud.overview', ['item'=>'non-system '.__('general.categories')]) }}
        @endslot

        @slot('action')
            {{-- <a class="btn btn-sm btn-danger" href="{{ route('categories.fix', 1) }}" title="Fix broken links and replace list items with other children than only list_items by labels" >Fix taxonomy</a> --}}
        @endslot

        @slot('bodyClass')

        @endslot

        @slot('body')
        <div class="row">
            
            <div class="col-sm-12">
                <h4>
                    {{__('general.Categories')}} ({{ $count }}) visual flat JSON basis. Items are sorted by {{ $locale_name }} translations. Categories fixed: {{ $fixed }}. 
                    <button onclick="createVisual()">Create visual</button>
                </h4>
                <textarea id="visual_data_source" onload="createVisual()" rows="20" style="width: 100%">{!! $catsJson !!}</textarea>
            </div>

            <div class="col-sm-12">
                <h4>
                    {{__('general.Category')}} tree JSON. Items are sorted by {{ $locale_name }} translations; (trans) parameter. 
                </h4>
                <textarea rows="20" style="width: 100%">{!! $filtered_json !!}</textarea>
            </div>

            <div class="col-sm-12">
                <h4>
                    {{__('general.Categories')}} ({{ $count }})
                </h4>

                <style>
                .node circle {
                  fill: #999;
                }
                .node text {
                  font-size: 9px;
                }
                .node--internal circle {
                  fill: #555;
                }
                .node--internal text {
                  text-shadow: 0 1px 0 #fff, 0 -1px 0 #fff, 1px 0 0 #fff, -1px 0 0 #fff;
                }
                .link {
                  fill: none;
                  stroke: #555;
                  stroke-opacity: 0.4;
                  stroke-width: 1.5px;
                }

                .bee_colony text, .bee_colony circle {
                    fill: #8A6E16;
                }
                .link.bee_colony{
                    stroke: #8A6E16;
                }

                .disorder text, .disorder circle{
                    fill: #7D1E0C;
                }
                .link.disorder{
                    stroke: #7D1E0C;
                }

                .hive text, .hive circle{
                    fill: #193E26;
                }
                .link.hive{
                    stroke: #193E26;
                }

                .food text, .food circle{
                    fill: #0D5308;
                }
                .link.food{
                    stroke: #0D5308;
                }

                .production text, .production circle{
                    fill: #8C581D;
                }
                .link.production{
                    stroke: #8C581D;
                }

                .apiary text, .apiary circle{
                    fill: #1C1A4A;
                }
                .link.apiary{
                    stroke: #1C1A4A;
                }

                .beekeeper text, .beekeeper circle{
                    fill: #40114A;
                }
                .link.beekeeper{
                    stroke: #40114A;
                }

                .space text, .space circle{
                    fill: #40114A;
                }
                .link.space{
                    stroke: #40114A;
                }

                .weather text, .weather circle{
                    fill: #296387;
                }
                .link.weather{
                    stroke: #296387;
                }

                
                </style>

                <svg width="1600" height="1600"></svg>
                <script src="https://d3js.org/d3.v4.min.js"></script>
                <script>
                function project(x, y) {
                  var angle = (x - 90) / 180 * Math.PI, radius = y;
                  return [radius * Math.cos(angle), radius * Math.sin(angle)];
                }

                function createVisual()
                {
                    var data = JSON.parse($('#visual_data_source').val());

                    var svg = d3.select("svg"),
                        width = +svg.attr("width"),
                        height = +svg.attr("height"),
                        g = svg.append("g").attr("transform", "translate(" + (width / 2 - 15) + "," + (height / 2 + 25) + ")");

                    var stratify = d3.stratify()
                        .parentId(function(d) { return d.id.substring(0, d.id.lastIndexOf(".")); });

                    var tree = d3.cluster()
                        .size([360, 390])
                        .separation(function(a, b) { return (a.parent == b.parent ? 1 : 2) / a.depth; });

                    var root = tree(stratify(data)
                        .sort(function(a, b) { return (a.height - b.height) || a.id.localeCompare(b.id); }));

                    var link = g.selectAll(".link")
                        .data(root.descendants().slice(1))
                        .enter().append("path")
                          .attr("class", function(d){ return "link "+d.data.base; })
                          .attr("d", function(d) {
                            return "M" + project(d.x, d.y)
                                + "C" + project(d.x, (d.y + d.parent.y) / 2)
                                + " " + project(d.parent.x, (d.y + d.parent.y) / 2)
                                + " " + project(d.parent.x, d.parent.y);
                          });

                    var node = g.selectAll(".node")
                        .data(root.descendants())
                        .enter().append("g")
                          .attr("class", function(d) { return "node" + (d.children ? " node--internal" : " node--leaf") + " "+d.data.base; })
                          .attr("transform", function(d) { return "translate(" + project(d.x, d.y) + ")"; });

                    node.append("circle")
                        .attr("r", 2.5);

                    node.append("text")
                        .attr("dy", ".31em")
                        .attr("x", function(d) { return d.x < 180 === !d.children ? 6 : -6; })
                        .style("text-anchor", function(d) { return d.x < 180 === !d.children ? "start" : "end"; })
                        .attr("transform", function(d) { return "rotate(" + (d.x < 180 ? d.x - 90 : d.x + 90) + ")"; })
                        .text(function(d) { return d.id.substring(d.id.lastIndexOf(".") + 1); });
                }
                
                </script>
            </div>

        </div>
        @endslot

    @endcomponent

@endsection