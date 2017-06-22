<?php

namespace Drupal\book_visit\Service;

use Drupal\Core\Database\Connection;

class BookingStorage
{
	const STATUS_PENDING = 0;

	const STATUS_CONFIRMED = 1;

	private $database;

	public function __construct(Connection $database)
	{
		$this->database = $database;
	}

	public function getAllBookings($header)
	{
		$query = $this->database->select('bookings', 'b')
			->extend('Drupal\Core\Database\Query\PagerSelectExtender')
			->limit(25)
			->extend('Drupal\Core\Database\Query\TableSortExtender')
			->orderByHeader($header);

		$query->fields('b', array(
			'id',
			'entity_id',
			'first_name',
			'last_name',
			'email',
			'date',
			'status',
			'created'
		))->orderBy('created', 'DESC');

		return $query->execute()->fetchAll();
	}

	public function addNewBooking(array $booking)
	{	
		try {
			$this->database->insert('bookings')
				->fields($booking)
				->execute();
		}
		catch (\Exception $e) {
		  throw $e;
		}
	}

	public function findOneById($booking_id) 
	{
		$query = $this->database->select('bookings', 'b')
			->fields('b')
			->condition('b.id', $booking_id);

		return $query->execute()->fetch();
	}

	public function delete($booking_id)
	{
		try {
			$this->database->delete('bookings')
				->condition('id', $booking_id)
			  ->execute();
		}
		catch (\Exception $e) {
		  throw $e;
		}
	}

	public function confirmStatus($booking_id)
	{
		try {
		  $this->database->update('bookings')
		    ->fields(array('status' => BookingStorage::STATUS_CONFIRMED))
		    ->condition('id', $booking_id)
		    ->execute();
		}
		catch (\Exception $e) {
		  throw $e;
		}
	}

	public function getPendingBookings($nid)
	{
		$query = $this->database->select('bookings', 'b')
			->fields('b')
			->condition('b.entity_id', $nid)
			->condition('b.status', BookingStorage::STATUS_PENDING);

		return $query->countQuery()->execute()->fetchField();
	}
}