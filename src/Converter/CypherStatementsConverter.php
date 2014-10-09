<?php

namespace Neoxygen\Neogen\Converter;

use Neoxygen\Neogen\Graph\Graph;

class CypherStatementsConverter implements ConverterInterface
{
    private $constraintsStatements = [];

    private $nodeStatements = [];

    private $edgeStatements = [];

    public function convert(Graph $graph)
    {
        $labels = [];
        $nodesByLabel = [];
        $edgesByType = [];
        $edgeTypes = [];

        foreach ($graph->getNodes() as $node) {
            $nodesByLabel[$node['label']][] = $node;
            if (!in_array($node['label'], $labels)) {
                $labels[] = $node['label'];
            }
        }

        // Creating constraints statements
        foreach ($labels as $label) {
            $identifier = strtolower($label);

            $ccs = 'CREATE CONSTRAINT ON (' . $identifier . ':' . $label . ') ASSERT ' . $identifier . '.neogen_id IS UNIQUE';
            $cst = ['statement' => $ccs];
            $this->constraintsStatements[] = $cst;
        }

        // Creating node creation statements
        foreach ($nodesByLabel as $key => $type) {
            if (!isset($type[0])){
                continue;
            }
            $node = $type[0];
            $idx = strtolower($key);
            $q = 'UNWIND {props} as prop'.PHP_EOL;
            $q .= 'MERGE ('.$idx.':'.$key.' {neogen_id: prop.neogen_id})'.PHP_EOL;
            $propsCount = count($node['properties']);
            if ($propsCount > 0) {
                $q .= 'SET ';
                $i = 1;
                foreach ($node['properties'] as $property => $value) {
                        $q .= $idx.'.'.$property.' = prop.properties.'.$property;
                        if ($i < $propsCount) {
                            $q .= ','.PHP_EOL;
                        }
                        $i++;
                }
            }

            $nts = [
                'statement' => $q,
                'parameters' => [
                    'props' => $nodesByLabel[$key]
                ]
            ];
            $this->nodeStatements[] = $nts;
        }

        foreach ($graph->getEdges() as $edge){
            if (!in_array($edge['type'], $edgeTypes)){
                $edgeTypes[] = $edge['type'];
            }
            $edgesByType[$edge['type']][] = $edge;
        }

        // Creating edge statements
        $i = 1;
        foreach ($edgesByType as $type => $rels){
            if (!isset($type[0])){
                continue;
            }
            $rel = $rels[0];
            $q = 'UNWIND {pairs} as pair'.PHP_EOL;
            $q .= 'MATCH (start {neogen_id: pair.source}), (end {neogen_id: pair.target})'.PHP_EOL;
            $q .= 'MERGE (start)-[edge:'.$type.']->(end)'.PHP_EOL;
            $propsCount = count($rel['properties']);
            if ($propsCount > 0) {
                $q .= 'SET ';
                $i = 1;
                foreach ($rel['properties'] as $property => $value) {
                    $q .= 'edge.'.$property.' = pair.properties.'.$property;
                    if ($i < $propsCount) {
                        $q .= ','.PHP_EOL;
                    }
                }
            }
            $ets = [
                'statement' => $q,
                'parameters' => [
                    'pairs' => $edgesByType[$type]
                ]
            ];
            $this->edgeStatements[] = $ets;
            $i++;
        }

    }

    public function getStatements()
    {
        $statements = array(
            'constraints' => $this->constraintsStatements,
            'nodes' => $this->nodeStatements,
            'edges' => $this->edgeStatements
        );

        return $statements;
    }

    public function getEdgeStatements()
    {
        return $this->edgeStatements;
    }

    public function getConstraintStatements()
    {
        return $this->constraintsStatements;
    }

    public function getNodeStatements()
    {
        return $this->nodeStatements;
    }
}