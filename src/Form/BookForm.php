<?php 

namespace Drupal\book_visit\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\FormBase;
use Drupal\book_visit\Service\BookingStorage;
use Symfony\Component\DependencyInjection\ContainerInterface;

class BookForm extends FormBase
{
	private $bookingStorage;

	function __construct(BookingStorage $bookingStorage)
	{
		$this->bookingStorage = $bookingStorage;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getFormId()
	{
		return 'book_visit';
	}

	/**
	 * {@inheritdoc}
	 */
	public function buildForm(array $form, FormStateInterface $form_state)
	{
		$form = array();

		$form['exibition'] = array(
			'#type' => 'entity_autocomplete',
			'#title' => $this->t('Exibition'),
			'#target_type' => 'node',
			'#bundles' => array('exibition'),
			'#required' => TRUE,
		);

		$form['first_name'] = array(
		  '#type' => 'textfield',
		  '#title' => $this->t('First Name'),
		  '#maxlength' => 255,
		  '#required' => TRUE,
		);

		$form['last_name'] = array(
		  '#type' => 'textfield',
		  '#title' => $this->t('last Name'),
		  '#maxlength' => 255,
		  '#required' => TRUE,
		);

		$form['email'] = array(
			'#type' => 'email',
			'#title' => $this->t('Email'),
			'#required' => TRUE,
		);

		$form['phone_number'] = array(
			'#type' => 'tel',
			'#title' => $this->t('Phone Number'),
		);

		$form['lan_pref'] = array(
			'#type' => 'select',
			'#title' => $this->t('Language preference'),
			'#options' => array(
				'en' => $this->t('English'),
				'fr' => $this->t('French'),
			),
			'#default_value' => 'en',
			'#required' => TRUE,
		);

		$form['date'] = array(
			'#type' => 'date',
			'#title' => $this->t('Date of visit'),
			'#required' => TRUE,
		);

		$form['number_of_participants'] = array(
			'#type' => 'number',
			'#title' => $this->t('Number of participants'),
			'#min' => 3,
			'#max' => \Drupal::config('book_visit.settings')->get('max_participants'),
			'#required' => TRUE,
		);

		$form['notes'] = array(
			'#type' => 'textarea',
			'#title' => $this->t('Additional notes'),
		);

		$form['submit'] = array(
		  '#type' => 'submit',
		  '#value' => $this->t('Submit'),
		);

	  return $form;
	}

	/**
	 * {@inheritdoc}
	 */
	public function validateForm(array &$form, FormStateInterface $form_state)
	{
		// Validate phone number.
		$phone_number = $form_state->getValue('phone_number');
		
		if (!empty($phone_number) && strlen($phone_number) != 10) {
			$form_state->setErrorByName('phone_number', $this->t('Please enter a valid 10 digit phone number.'));
		}

		// Validate date.
		$submittedDate = (string) $form_state->getValue('date');

		if (new \DateTime($submittedDate) <= new \DateTime()) {
			$form_state->setErrorByName('date', $this->t('Please select a future date'));
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function submitForm(array &$form, FormStateInterface $form_state)
	{
		// Get submitted values.
		$values = $form_state->getValues();

		// Map database fields to values 
		$booking = [
			'entity_id' => $values['exibition'],
			'first_name' => $values['first_name'],
			'last_name' => $values['last_name'],
			'email' => $values['email'],
			'phone_number' => $values['phone_number'],
			'lan' => $values['lan_pref'],
			'date' => $values['date'],
			'number_of_participants' => $values['number_of_participants'],
			'notes' => $values['notes'],
			'status' => BookingStorage::STATUS_PENDING,
			'created' => REQUEST_TIME,
		];

		// Save values to database.
		$this->bookingStorage->addNewBooking($booking);

		// Output a Success message.
		drupal_set_message($this->t('Thanks!. We will get in touch with you shortly.'));
	}

	public static function create(ContainerInterface $container)
	{
	  return new static(
	  	$container->get('book_visit.booking_storage')
	  );
	}
}