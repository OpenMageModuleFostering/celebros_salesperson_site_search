<?php
/**
 * Celebros Qwiser - Magento Extension
 *
 * @category    Celebros
 * @package     Celebros_Salesperson
 * @author		Omniscience Co. - Dan Aharon-Shalom (email: dan@omniscience.co.il)
 *
 */
class Celebros_Salesperson_Model_Mysql4_Qwiser extends Mage_Core_Model_Mysql4_Abstract
{
	
	public function _construct()
    {
        $this->_init('rating/rating', 'rating_id');
    }
	
	/**
     * Get rating entity type id by code
     *
     * @param string $entityCode
     * @return int
     */
    public function getRateingByEntityId($entityId)
    {
        $select = $this->_getReadAdapter()->select()
            ->from( $this->getTable('rating_vote'), array('percent'))
            ->where('entity_pk_value = ?', $entityId);
        return $this->_getReadAdapter()->fetchAll($select);
    }
}