<?php

namespace EXSyst\Component\IO;

/**
 * Represents the state in which a source or sink was at a previous time.
 *
 * @author Nicolas "Exter-N" L. <exter-n@exter-n.fr>
 *
 * @api
 */
interface StateInterface
{
    /**
     * Restores the source or sink to which this state belongs to this state.
     *
     * @throws Exception\RuntimeException If an I/O operation fails
     * @throws Exception\LogicException   If an earlier state has already been restored
     *
     * @return $this This state, for method chaining
     *
     * @api
     */
    public function restore();
}
