# Se7ety

--- Functional Requirements

  -- Donor Management
    - Donations: Donors can contribute money and medical support.
    - Track Donations: Manage information on donors, including donation amounts and types.
    - Receipts & Tax: Generate receipts and tax information for donations.

  -- Volunteer Management
    - Assign Volunteers: Assign volunteers to tasks and events.
    - Track Volunteer Hours: Keep a record of volunteer hours and activities.
    - Certificates: Generate volunteer certificates based on hours worked and participation.

  -- Event Management
    - Event Lifecycle: Create, manage, and track events.
    - Registration & Ticketing: Handle event registration, ticketing, and attendance tracking.
    - Communication: Send reminders and communications for event updates.

  -- Beneficiary Management
    - Store Information: Maintain detailed records of beneficiaries.
    - Program Monitoring: Evaluate the impact of programs on beneficiaries.

  -- Communication
    - Notifications: Send email notifications and SMS messages for updates and reminders.

    
--- Design Patterns Used
  1. Singleton Design Pattern
    The DBConnection class follows the Singleton Design Pattern:
      
      Purpose: Ensure only one instance of the database connection exists throughout the application.
      Implementation:
      The constructor is private, preventing direct instantiation.
      A static method getInstance() provides access to the single instance.
      Benefits:
      Minimizes redundant connection overhead.
      Ensures consistency in database operations.
      Simplifies maintenance with centralized connection logic.
      Methods:
      run_queries, run_query, run_select_query: Execute and manage database queries efficiently.

  2. Observer Design Pattern
    The Observer Design Pattern is used for event notifications:

      Classes:
      Event: Acts as the Subject, managing a list of Observers.
      EventReminder & Notification: Act as Observers that register with the Event.
      Mechanism:
      Observers are notified when the Event state changes via the notifyObservers() method.
      Benefits:
      Efficient communication between the Subject and Observers.
      Keeps Observers synchronized with updates in the Event class.

  3. Decorator Design Pattern
    The Decorator Design Pattern dynamically extends the functionality of the base Receipt class:
    
      Classes:
      BasicReceipt: Provides core receipt details.
      MedicalReceiptDecorator & MoneyReceiptDecorator: Add features for medical item calculations and monetary donations.
      TotalDecorator: Combines enhancements to generate comprehensive receipts.
      Benefits:
      Modular extension of features.
      Maintains the base class's simplicity and flexibility.

  4. Strategy Design Pattern
    The Strategy Pattern manages different donation and communication methods:
    
      Donation Method:
      Interface: DonationMethodStrategy defines the processDonation method.
      Implementations:
      InKindDonation, EWalletDonation, ChequeDonation, CashDonation: Handle various donation types.
      Donation Management:
      The Donation class delegates processing to the appropriate strategy.
      Supports specialized donations: MedicalDonation and MoneyDonation.
      Communication:
      Interface: CommunicationStrategy defines the send_communication method.
      Implementations: Email, SocialMedia, SMS: Manage different message-sending processes.
      Aggregation: The Communication class uses a CommunicationStrategy instance for adaptable communication methods.
      Benefits:
      Flexibility in adding new donation and communication types.
      Scalable and maintainable design.
