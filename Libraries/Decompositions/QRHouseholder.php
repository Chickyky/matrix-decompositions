<?php

namespace Libraries\Decompositions;

/**
 * Helper class to provide methods concerning the lu decomposition.
 *
 * @author  David Stutz
 * @license http://www.gnu.org/licenses/gpl-3.0
 */
class QRHouseholder {
    
    /**
     * @var matrix
     */
    protected $_matrix;
    
    /**
     * @var vector
     */
    protected $_tau;
    
    /**
     * Constructor: Get the qr decomposition of the given matrix using householder transformations.
     * The single householder matrizes are stores within the matrix.
     *
     * @param matrix  matrix to get the composition of
     */
    public function __construct(&$matrix) {
        new \Libraries\Assertion($matrix instanceof \Libraries\Matrix, 'Given matrix not of class Matrix.');

        $this->_tau = new \Libraries\Vector($matrix->columns());
        $this->_matrix = $matrix->copy();

        for ($j = 0; $j < $this->_matrix->columns(); $j++) {

            $norm = 0.;
            for ($i = $j; $i < $this->_matrix->rows(); $i++) {
                $norm += pow($this->_matrix->get($i, $j), 2);
            }
            $norm = sqrt($norm);

            $sign = 1.;
            if ($this->_matrix->get($j, $j) < 0) {
                $sign = -1.;
            }

            $v1 = $this->_matrix->get($j, $j) + $sign * $norm;
            $scalar = 1;

            for ($i = $j + 1; $i < $this->_matrix->rows(); $i++) {
                $this->_matrix->set($i, $j, $this->_matrix->get($i, $j) / $v1);
                $scalar += pow($this->_matrix->get($i, $j), 2);
            }

            $this->_tau->set($j, 2. / $scalar);

            $w = new \Libraries\Vector($this->_matrix->columns());
            $w->setAll(0.);

            // First calculate w = v_j^T * A.
            for ($i = $j; $i < $this->_matrix->columns(); $i++) {
                $w->set($i, $this->_matrix->get($j, $i));
                for ($k = $j + 1; $k < $this->_matrix->rows(); $k++) {
                    if ($i == $j) {
                        $w->set($i, $w->get($i) + $this->_matrix->get($k, $j) * $this->_matrix->get($k, $i) * $v1);
                    }
                    else {
                        $w->set($i, $w->get($i) + $this->_matrix->get($k, $j) * $this->_matrix->get($k, $i));
                    }
                }

                $this->_matrix->set($j, $i, $this->_matrix->get($j, $i) - $this->_tau->get($j) * $w->get($i));
                for ($k = $j + 1; $k < $this->_matrix->rows(); $k++) {
                    if ($i > $j) {
                        $this->_matrix->set($k, $i, $this->_matrix->get($k, $i) - $this->_tau->get($j) * $this->_matrix->get($k, $j) * $w->get($i));
                    }
                }
            }
        }
    }
    
    /**
     * Assembles Q using the single givens rotations.
     * 
     * @return  matrix  Q
     */
    public function getQ() {
        // Q is an mxm matrix if m is the maximum of the number of rows and thenumber of columns.
        $m = max($this->_matrix->columns(), $this->_matrix->rows());
        $Q = new \Libraries\Matrix($m, $m);
        $Q->setAll(0.);
        
        // TODO: assemble Q.
        
        return $Q;
    }
    
    /**
     * Gets the upper triangular matrix R.
     */
    public function getR() {
        $R = $this->_matrix->copy();
        
        for ($i = 0; $i < $R->rows(); $i++) {
            for ($j = 0; $j < $i; $j++) {
                $R->set($i, $j, 0);
            }
        }
        
        // Resize R to a square matrix.
        $n = min($R->rows(), $R->columns());
        return $R->resize($n, $n);
    }
}