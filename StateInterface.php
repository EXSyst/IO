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
     * @return self This state, for method chaining
     *
     * @throws \RuntimeException If an I/O operation fails
     * @throws \LogicException   If an earlier state has already been restored
     *
     * @api
     */
    public function restore();
}
