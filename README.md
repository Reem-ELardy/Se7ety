# Se7ety


## Table of Contents
- [Client Requirements ](#Client-Requirements)
- [Design Pattern](#Design-Patterns)
  - [Strategy Pattern](#Strategy-Pattern)
  - [Observer Pattern](#Observer-Pattern)
  - [Decorator Pattern](#Decorator-Pattern)
  - [Factory Pattern](#Factory-Pattern)
  - [Signletone Pattern](#Singletone-Pattern)
  
## Client Requirements

- Donor donates money, and medical supports
- Track donor info, donation amounts, donation types
- Generate receipts and tax
- Assign volunteers to tasks and events
- Track volunteer info, skills and availability
- Track volunteer hours
- Generate volunteer certificates
- Create, manage, and track events
- Handle event registration, ticketing, and attendance tracking 
- Send event reminders and communications 
- Store information about patient
- Store patient needs
- Monitor and evaluate the impact of programs on beneficiaries
- Send email notifications, SMS messages

## Design Patterns

### Strategy Pattern

#### Donation
 - Applied to handle multiple donation methods.
 - The DonationMethodStrategy interface defines a processDonation method, implemented by concrete classes such as InKindDonation, EWalletDonation, ChequeDonation, and CashDonation.
 - The Donation class delegates the processing logic to these strategies based on the selected donation method, promoting flexibility and scalability for adding new donation types.
 - It also supports specialized donations, like MedicalDonation and MoneyDonation, also used to manage multiple communication methods.
#### Communication
 - The CommunicationStrategy interface defines the send_communication method, which is implemented by concrete classes like Email, SocialMedia, and SMS. The
 - Communication class aggregates a CommunicationStrategy instance to delegate the message-sending process, making it adaptable to different communication types.

---

### Observer Pattern

#### Event
 - The Event class serves as the Subject, managing a list of Observers and notifying them of updates.
 - The EventReminder and Notification classes act as Observers that register with the Event to receive notifications about changes.
 - When the Event state is updated, it triggers the notifyObservers() method, keeping the Observers synchronized.
 - This design ensures efficient communication between the Subject and its Observers.

---
     
### Decorator Pattern

#### Receipt
 - Applied to extend the functionality of a base Receipt class dynamically.
 - The BasicReceipt provides core receipt details, while decorators like MedicalReceiptDecorator and MoneyReceiptDecorator add specific features such as medical item calculations or monetary donations.
 - The TotalDecorator combines these enhancements to generate comprehensive receipts, enabling flexible and modular extension of features without altering the base class.

---
     
### Factory Pattern

#### Login
 - The factory simplifies the creation of specific types of users (Volunteer, Donor, and Patient) while keeping the client code decoupled from the concrete class implementations..
 - Handles the login process for users.
 - Instantiates the correct class based on the provided role and calls the login method.
 - Returns the user object if login is successful, or null otherwise.
   
#### Signup
 - Handles the signup process for users.
 - Creates a specific user object (e.g., Volunteer, Donor, or Patient) based on the role.
 - Calls the signup method and returns the newly created user object if successful, or null if it fails.

---

### Singleton Pattern

#### Database
 - Applied to ensures that only one instance of the database connection exists throughout the application.
 - This is achieved by making the constructor private, thereby preventing direct instantiation, and providing a static method getInstance() to manage the single instance of the class.
 - By centralizing database connection logic in this way, the class minimizes redundant connection overhead, ensures consistency across database operations, and simplifies maintenance.
 - Additionally, it provides methods like run_queries, run_query, and run_select_query for executing database queries, promoting reusability and clean abstraction for database interactions.


