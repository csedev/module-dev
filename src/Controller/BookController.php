<?php

namespace Drupal\book_visit\Controller;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Datetime\DateFormatter;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Link;
use Drupal\book_visit\Service\BookingStorage;

class BookController extends Controllerbase
{
	protected $bookingStorage;

	protected $dateFormatter;

	protected $entityManager;

	public function __construct(BookingStorage $bookingStorage, DateFormatter $dateFormatter, EntityManagerInterface $entityManager)
	{
		$this->bookingStorage = $bookingStorage;
		$this->dateFormatter = $dateFormatter;
		$this->entityManager = $entityManager;
	}

	/* List all bookings.
	 * path: /admin/bookings
	 */
	public function listAction()
	{
		$header[] = array(
			'data' => $this->t('First Name'),
			'field' => 'first_name',
		);
		$header[] = array(
			'data' => $this->t('Last Name'),
			'field' => 'last_name',
		);
		$header[] = array(
			'data' => $this->t('Email'),
			'field' => 'email',
		);
		$header[] = array(
			'data' => $this->t('Exibition'),
			'field' => 'entity_id',
		);
		$header[] = array(
			'data' => $this->t('Date'),
			'field' => 'date',
		);
		$header[] = array(
			'data' => $this->t('Status'),
			'field' => 'status',
		);
		$header[] = array(
			'data' => $this->t('created'),
			'field' => 'created',
			'sort' => 'desc',
		);

		$bookings = $this->bookingStorage->getAllBookings($header);

		$entity_ids = array_map(function($booking) {
			return $booking->entity_id;
		}, $bookings);

		$storage = $this->entityManager->getStorage('node');
		$nodes = $storage->loadMultiple($entity_ids);

		$link_options = [
			'attributes' => [
				'target' => '_blank',
			], 
		];

		$rows = [];

		foreach ($bookings as $booking) {
			$link = Link::createFromRoute($booking->first_name, 'book_visit.show', ['booking_id' => $booking->id], $link_options);

			$rows[$booking->id]['first_name'] = $link;
			$rows[$booking->id]['last_name'] = $booking->last_name;
			$rows[$booking->id]['email'] = $booking->email;
			$rows[$booking->id]['exibition'] = $nodes[$booking->entity_id]->getTitle();

			$rows[$booking->id]['date'] = $this->dateFormatter
				->format(strtotime($booking->date));
			
			$rows[$booking->id]['status'] = ($booking->status) ? $this->t('Confirmed') : $this->t('Pending');
			
			$rows[$booking->id]['created'] = $this->dateFormatter
				->format($booking->created);
		}

		$table = [
			'#theme' => 'table',
			'#header' => $header,
			'#rows' => $rows,
			'#sticky' => FALSE,
			'#empty' => $this->t('There are no bookings at this time.'),
		];

		$markup = drupal_render($table);

		$pager = [
			'#theme' => 'pager',
			// The number of pages in the list.
			'#quantity' => count($rows),
			// The name of the route to be used to build pager links. By default no
			// path is provided, which will make links relative to the current URL.
			// This makes the page more effectively cacheable.
			'#route_name' => '<none>',
			// An associative array of query string parameters to append to the pager
			// links.
			'#parameters' => [],
			// An array of labels for the controls in the pager.
			'#tags' => [],
			// The pager ID, to distinguish between multiple pagers on the same page.
			'#element' => 0,
		];

		$markup .= drupal_render($pager);

		return [
			'#type' => 'markup',
			'#markup' => $markup
		];
	}

	/* List all bookings.
	 * path: /booking/{booking_id}
	 */
	public function showAction($booking_id)
	{
		$booking = $this->bookingStorage->findOneById($booking_id);
		
		if (!$booking) {
			// Return 404 page.
			throw new NotFoundHttpException();
		}

		//Print node title instead of node id
		$storage = $this->entityManager->getStorage('node');
		$node = $storage->load($booking->entity_id);
		$booking->entity_id = $node->getTitle();

		return [
			'#theme' => 'booking',
			'#content' => $booking,
			'#attached' => array(
				'library' => array(
					'book_visit/book_visit',
				),
			),
		];
	}

	/* Cancel a booking.
	 * path: /booking/{booking_id}/cancel
	 */
	public function cancelAction($booking_id)
	{
		$this->bookingStorage->delete($booking_id);

		drupal_set_message($this->t('Booking @id was successfully canceled', array('@id' => $booking_id)));

		return $this->redirect('book_visit.list');
	}

	/* Confirm a booking.
	 * path: /booking/{booking_id}/confirm
	 */
	public function confirmAction($booking_id)
	{
		$this->bookingStorage->confirmStatus($booking_id);

		drupal_set_message($this->t('Booking @id is confirmed!', array('@id' => $booking_id)));

		return $this->redirect('book_visit.show', ['booking_id' => $booking_id]);
	}

	public static function create(ContainerInterface $container)
	{
	  return new static(
	  	$container->get('book_visit.booking_storage'),
	  	$container->get('date.formatter'),
	  	$container->get('entity.manager')
	  );
	}
}