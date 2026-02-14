# Tabulation System Test Guide
## Miss Universe-Style Pageant Flow

This guide provides step-by-step instructions and sample data to test the tabulation system with a Miss Universe-style pageant structure.

---

## 📋 Test Flow Overview

1. **Create Event** - Set up the pageant (creator is auto-assigned)
2. **Assign Users to Event** - Assign staff to see/manage the event
3. **Create Levels** - Preliminaries, Semi-finals, Finals
4. **Create Rounds** - Talent, Evening Gown, Swimsuit, Q&A
5. **Create Criteria** - Scoring criteria for each round
6. **Assign Criteria to Rounds** - Link criteria with weights
7. **Add Contestants** - Register participants
8. **Create Event Staff** - Event Organizer and Technical Admin
9. **Create Judge Accounts** - Set up judges
10. **Assign Judges to Event** - Link judges to pageant (auto-assigned to event)
11. **Assign Judges to Rounds** - Give judges their scoring assignments
12. **Judge Scoring** - Judges score contestants
13. **Calculate Results** - Generate rankings
14. **View Results** - Display final rankings

---

## 👤 Step 0: Create Event Staff (Optional but Recommended)

**Navigation:** Users → Add New User

**Note:** As Super Admin, you can create Event Organizer and Event Technical Admin first, then delegate tasks to them. This is optional - you can also do everything as Super Admin.

### Create Event Organizer

The Event Organizer manages the overall event, coordinates staff, and oversees the event.

```
Full Name: Patricia Thompson
Username: organizer1
Email: organizer@pageant.local
Password: organizer123
Role: Event Organizer
```

### Create Event Technical Admin

The Event Technical Admin handles technical setup: criteria, rounds, weights, and system configuration.

```
Full Name: James Wilson
Username: techadmin1
Email: techadmin@pageant.local
Password: techadmin123
Role: Event Technical Admin
```

**Role Hierarchy:**
- **Super Admin**: Can create all roles
- **Event Organizer**: Can create Event Technical Admin, Tabulator, Judge, Auditor, Host
- **Event Technical Admin**: Can manage criteria, rounds, weights (technical setup)
- **Event Admin**: Can create Tabulator, Judge, Auditor, Host

---

## 🎯 Step 1: Create Event

**Navigation:** Events → Create Event

**Note:** Can be done by Super Admin, Event Organizer, or Event Admin

**Important:** When you create an event, you are **automatically assigned** to that event. This means:
- ✅ You will see this event in your events list
- ✅ You can manage this event
- ✅ Other users (except Super Admin) won't see this event until you assign them

### Sample Event Data

```
Name: Miss Universe 2024
Description: The 73rd Miss Universe pageant showcasing beauty, talent, and intelligence
Event Type: Pageant
Venue: Grand Ballroom, International Convention Center
Event Date: 2024-12-15
Start Time: 19:00
End Time: 23:00
Status: Draft (change to "Ongoing" when ready)
```

**After creating the event:**
- The creator is automatically assigned to the event
- You can now assign other users to this event (see Step 1a below)

---

## 👥 Step 1a: Assign Users to Event (Important!)

**Navigation:** Events → [Select Event] → Assign Users to Event

**Note:** Only Super Admin and Event Organizer can assign users to events

**Why is this important?**
- Users (except Super Admin) can **only see events they're assigned to**
- If a user can't see an event, they can't manage it
- Judges are automatically assigned when assigned as judges, but other staff need manual assignment

### Assign Event Staff to Event

After creating "Miss Universe 2024", assign the Event Organizer and Event Technical Admin:

1. Go to the event details page
2. Click **"Assign Users to Event"** button
3. Assign:
   - **Event Organizer** (organizer1) - if not already assigned
   - **Event Technical Admin** (techadmin1) - if not already assigned

**Note:** 
- When you create an event, you (the creator) are automatically assigned
- When you assign a judge to an event, they are automatically assigned to see that event
- Other users (Event Admin, Tabulator, etc.) need to be manually assigned

---

## 🏆 Step 2: Create Levels

**Navigation:** Events → [Select Event] → Manage Levels

### Level 1: Preliminaries

```
Name: Preliminaries
Description: Initial competition rounds to select semi-finalists
Order: 1
```

### Level 2: Semi-Finals

```
Name: Semi-Finals
Description: Top 20 contestants compete for final 10 spots
Order: 2
```

### Level 3: Finals

```
Name: Finals
Description: Final competition to determine the winner
Order: 3
```

---

## 🎭 Step 3: Create Rounds

**Navigation:** Levels → [Select Level] → Manage Rounds

### Under "Preliminaries" Level:

#### Round 1: Talent Competition
```
Name: Talent Competition
Description: Contestants showcase their unique talents
Order: 1
Status: Active
```

#### Round 2: Swimsuit Competition
```
Name: Swimsuit Competition
Description: Physical fitness and confidence on stage
Order: 2
Status: Active
```

#### Round 3: Evening Gown
```
Name: Evening Gown
Description: Elegance, poise, and style presentation
Order: 3
Status: Active
```

### Under "Semi-Finals" Level:

#### Round 4: Top 20 Interview
```
Name: Top 20 Interview
Description: Personal interview with judges
Order: 1
Status: Active
```

#### Round 5: Swimsuit (Semi-Finals)
```
Name: Swimsuit (Semi-Finals)
Description: Semi-final swimsuit presentation
Order: 2
Status: Active
```

#### Round 6: Evening Gown (Semi-Finals)
```
Name: Evening Gown (Semi-Finals)
Description: Semi-final evening gown presentation
Order: 3
Status: Active
```

### Under "Finals" Level:

#### Round 7: Final Q&A
```
Name: Final Q&A
Description: Final question and answer round for top 5
Order: 1
Status: Active
```

---

## 📊 Step 4: Create Criteria

**Navigation:** Criteria → Create Criteria

### Criteria List (Create all of these):

#### 1. Talent Criteria
```
Name: Performance Quality
Description: Technical skill, execution, and overall performance quality
Max Score: 100
```

```
Name: Originality
Description: Uniqueness and creativity of the talent
Max Score: 50
```

```
Name: Stage Presence
Description: Confidence, charisma, and audience engagement
Max Score: 50
```

#### 2. Swimsuit Criteria
```
Name: Physical Fitness
Description: Overall physical condition and health
Max Score: 50
```

```
Name: Confidence
Description: Poise, confidence, and stage presence
Max Score: 50
```

```
Name: Overall Presentation
Description: Overall appearance and presentation
Max Score: 50
```

#### 3. Evening Gown Criteria
```
Name: Elegance
Description: Grace, poise, and elegant presentation
Max Score: 50
```

```
Name: Style & Fashion
Description: Fashion sense and style choices
Max Score: 50
```

```
Name: Stage Presence
Description: Confidence and stage presence
Max Score: 50
```

#### 4. Interview Criteria
```
Name: Communication Skills
Description: Clarity, articulation, and communication ability
Max Score: 50
```

```
Name: Intelligence
Description: Depth of thought and intellectual capacity
Max Score: 50
```

```
Name: Personality
Description: Authenticity, charisma, and personality
Max Score: 50
```

#### 5. Q&A Criteria
```
Name: Answer Quality
Description: Relevance, depth, and quality of answer
Max Score: 50
```

```
Name: Poise Under Pressure
Description: Composure and confidence under pressure
Max Score: 50
```

```
Name: Communication
Description: Clarity and effectiveness of communication
Max Score: 50
```

---

## ⚖️ Step 5: Assign Criteria to Rounds with Weights

**Navigation:** Rounds → [Select Round] → Set Weights

### Round 1: Talent Competition
- **Performance Quality**: Weight 50%
- **Originality**: Weight 30%
- **Stage Presence**: Weight 20%
- **Total: 100%**

### Round 2: Swimsuit Competition
- **Physical Fitness**: Weight 40%
- **Confidence**: Weight 35%
- **Overall Presentation**: Weight 25%
- **Total: 100%**

### Round 3: Evening Gown
- **Elegance**: Weight 40%
- **Style & Fashion**: Weight 35%
- **Stage Presence**: Weight 25%
- **Total: 100%**

### Round 4: Top 20 Interview
- **Communication Skills**: Weight 35%
- **Intelligence**: Weight 35%
- **Personality**: Weight 30%
- **Total: 100%**

### Round 5: Swimsuit (Semi-Finals)
- **Physical Fitness**: Weight 40%
- **Confidence**: Weight 35%
- **Overall Presentation**: Weight 25%
- **Total: 100%**

### Round 6: Evening Gown (Semi-Finals)
- **Elegance**: Weight 40%
- **Style & Fashion**: Weight 35%
- **Stage Presence**: Weight 25%
- **Total: 100%**

### Round 7: Final Q&A
- **Answer Quality**: Weight 50%
- **Poise Under Pressure**: Weight 30%
- **Communication**: Weight 20%
- **Total: 100%**

---

## 👑 Step 6: Add Contestants

**Navigation:** Events → [Select Event] → Manage Contestants

### Sample Contestants (Add 25 contestants):

```
Contestant #1
Name: Emma Rodriguez
Team: United States
Category: North America
Status: Active

Contestant #2
Name: Sofia Martinez
Team: Mexico
Category: North America
Status: Active

Contestant #3
Name: Isabella Santos
Team: Brazil
Category: South America
Status: Active

Contestant #4
Name: Olivia Chen
Team: China
Category: Asia
Status: Active

Contestant #5
Name: Charlotte Dubois
Team: France
Category: Europe
Status: Active

Contestant #6
Name: Amelia Johnson
Team: Australia
Category: Oceania
Status: Active

Contestant #7
Name: Mia Williams
Team: Canada
Category: North America
Status: Active

Contestant #8
Name: Luna Garcia
Team: Spain
Category: Europe
Status: Active

Contestant #9
Name: Aria Patel
Team: India
Category: Asia
Status: Active

Contestant #10
Name: Zara Khan
Team: Pakistan
Category: Asia
Status: Active

Contestant #11
Name: Maya Thompson
Team: United Kingdom
Category: Europe
Status: Active

Contestant #12
Name: Layla Anderson
Team: South Africa
Category: Africa
Status: Active

Contestant #13
Name: Nova Brown
Team: Jamaica
Category: Caribbean
Status: Active

Contestant #14
Name: Stella Wilson
Team: Italy
Category: Europe
Status: Active

Contestant #15
Name: Aurora Lee
Team: South Korea
Category: Asia
Status: Active

Contestant #16
Name: Celeste Taylor
Team: Philippines
Category: Asia
Status: Active

Contestant #17
Name: Iris White
Team: Netherlands
Category: Europe
Status: Active

Contestant #18
Name: Rose Davis
Team: Argentina
Category: South America
Status: Active

Contestant #19
Name: Lily Miller
Team: Germany
Category: Europe
Status: Active

Contestant #20
Name: Violet Moore
Team: Thailand
Category: Asia
Status: Active

Contestant #21
Name: Jasmine Garcia
Team: Colombia
Category: South America
Status: Active

Contestant #22
Name: Daisy Martinez
Team: Venezuela
Category: South America
Status: Active

Contestant #23
Name: Poppy Anderson
Team: Sweden
Category: Europe
Status: Active

Contestant #24
Name: Tulip Johnson
Team: Japan
Category: Asia
Status: Active

Contestant #25
Name: Orchid Smith
Team: New Zealand
Category: Oceania
Status: Active
```

---

## 👨‍⚖️ Step 8: Create Judge Accounts

**Navigation:** Users → Add New User

**Note:** 
- Super Admin can create all roles
- Event Organizer can create: Event Technical Admin, Tabulator, Judge, Auditor, Host
- Event Admin can create: Tabulator, Judge, Auditor, Host

### Judge 1
```
Full Name: John Anderson
Username: judge1
Email: judge1@pageant.local
Password: judge123
Role: Judge
```

### Judge 2
```
Full Name: Maria Garcia
Username: judge2
Email: judge2@pageant.local
Password: judge123
Role: Judge
```

### Judge 3
```
Full Name: David Chen
Username: judge3
Email: judge3@pageant.local
Password: judge123
Role: Judge
```

### Judge 4
```
Full Name: Sarah Williams
Username: judge4
Email: judge4@pageant.local
Password: judge123
Role: Judge
```

### Judge 5
```
Full Name: Michael Brown
Username: judge5
Email: judge5@pageant.local
Password: judge123
Role: Judge
```

### Judge 6
```
Full Name: Lisa Johnson
Username: judge6
Email: judge6@pageant.local
Password: judge123
Role: Judge
```

### Judge 7
```
Full Name: Robert Taylor
Username: judge7
Email: judge7@pageant.local
Password: judge123
Role: Judge
```

---

## 🔗 Step 9: Assign Judges to Event

**Navigation:** Judges → Find judge in "All Judge Users" → Click "Assign to Event"

Assign all 7 judges to "Miss Universe 2024" event:
- Judge 1 → Event: Miss Universe 2024, Judge #: 1
- Judge 2 → Event: Miss Universe 2024, Judge #: 2
- Judge 3 → Event: Miss Universe 2024, Judge #: 3
- Judge 4 → Event: Miss Universe 2024, Judge #: 4
- Judge 5 → Event: Miss Universe 2024, Judge #: 5
- Judge 6 → Event: Miss Universe 2024, Judge #: 6
- Judge 7 → Event: Miss Universe 2024, Judge #: 7

**Important:** When you assign a judge to an event, they are **automatically assigned** to see that event. This means:
- ✅ Judges will see the event in their dashboard (if they have rounds assigned)
- ✅ Judges can access their assigned rounds for scoring
- ✅ No need to manually assign judges to events separately

---

## 📝 Step 10: Assign Judges to Rounds

**Navigation:** Judges → Find judge → Click "Assign Rounds" button

### Round 1: Talent Competition
Assign all 7 judges

### Round 2: Swimsuit Competition
Assign all 7 judges

### Round 3: Evening Gown
Assign all 7 judges

### Round 4: Top 20 Interview
Assign all 7 judges

### Round 5: Swimsuit (Semi-Finals)
Assign all 7 judges

### Round 6: Evening Gown (Semi-Finals)
Assign all 7 judges

### Round 7: Final Q&A
Assign all 7 judges

---

## ✍️ Step 11: Judge Scoring Test

**Login as Judge 1** (judge1 / judge123)

### Test Scoring for Round 1: Talent Competition

1. Go to **My Rounds**
2. Click **Score Contestants** on "Talent Competition"
3. Score Contestant #1 (Emma Rodriguez):
   - Performance Quality: 85
   - Originality: 42
   - Stage Presence: 45
   - Click **Submit Scores**

4. Score Contestant #2 (Sofia Martinez):
   - Performance Quality: 88
   - Originality: 45
   - Stage Presence: 48
   - Click **Submit Scores**

5. Continue scoring 5-10 contestants to test the system

### Sample Scoring Guide (for realistic testing):

**High Performers (Top 5):**
- Performance Quality: 85-95
- Originality: 40-48
- Stage Presence: 42-48

**Average Performers (Middle 10):**
- Performance Quality: 70-84
- Originality: 30-39
- Stage Presence: 30-41

**Lower Performers (Bottom 10):**
- Performance Quality: 60-69
- Originality: 25-29
- Stage Presence: 25-29

---

## 📊 Step 12: Calculate Results

**Navigation:** Events → [Select Event] → Results → Calculate

1. Select the round (e.g., "Talent Competition")
2. Click **Calculate Results**
3. System will:
   - Average scores from all judges
   - Calculate weighted totals
   - Generate rankings

---

## 🏅 Step 13: View Results

**Navigation:** Events → [Select Event] → Results

View the rankings and see:
- Contestant rankings
- Total scores
- Average scores
- Progress through rounds

---

## 🧪 Testing Scenarios

### Scenario 1: Complete Preliminaries
1. All judges score all 25 contestants in:
   - Talent Competition
   - Swimsuit Competition
   - Evening Gown
2. Calculate results for each round
3. Identify top 20 for semi-finals

### Scenario 2: Semi-Finals
1. Filter to top 20 contestants
2. Judges score in:
   - Top 20 Interview
   - Swimsuit (Semi-Finals)
   - Evening Gown (Semi-Finals)
3. Calculate results
4. Identify top 10 for finals

### Scenario 3: Finals
1. Filter to top 5 contestants
2. Judges score in:
   - Final Q&A
3. Calculate final results
4. Crown the winner!

---

## 📋 Quick Test Checklist

- [ ] Event created (creator auto-assigned)
- [ ] Users assigned to event (Event Organizer, Event Technical Admin)
- [ ] 3 Levels created (Preliminaries, Semi-Finals, Finals)
- [ ] 7 Rounds created
- [ ] 15 Criteria created
- [ ] Criteria assigned to rounds with weights
- [ ] 25 Contestants added
- [ ] Event Organizer created
- [ ] Event Technical Admin created
- [ ] 7 Judges created
- [ ] Judges assigned to event (auto-assigned to see event)
- [ ] Judges assigned to rounds
- [ ] Test scoring completed
- [ ] Results calculated
- [ ] Rankings viewed

---

## 💡 Tips for Testing

1. **Start Small**: Test with 3-5 contestants first
2. **Use Different Judges**: Login as different judges to test scoring
3. **Check Calculations**: Verify weighted scores are correct
4. **Test Edge Cases**: 
   - Same scores for multiple contestants
   - Very high/low scores
   - Missing scores
5. **Test Permissions**: Verify judges can only see their assigned rounds
6. **Test Results**: Check that rankings update correctly

---

## 🎯 Expected Outcomes

After completing all steps:
- ✅ 25 contestants registered
- ✅ 7 judges ready to score
- ✅ 7 rounds configured with criteria
- ✅ Scoring interface functional
- ✅ Results calculation working
- ✅ Rankings displayed correctly

---

## 👥 Role Management Guide

### Who Can Create What?

#### Super Admin
- ✅ Can create: All roles (Super Admin, Event Organizer, Event Admin, Event Technical Admin, Tabulator, Judge, Auditor, Host)
- ✅ Full system access
- ✅ Can see **ALL events** (no filtering)
- ✅ Can assign users to any event

#### Event Organizer
- ✅ Can create: Event Technical Admin, Tabulator, Judge, Auditor, Host
- ✅ Can manage: Events, Judges, Contestants, Results viewing
- ✅ Can assign users to events they manage
- ✅ Can see **only events they're assigned to**
- ❌ Cannot create: Super Admin, Event Admin, Event Organizer

#### Event Technical Admin
- ✅ Can manage: Criteria, Rounds, Weights (technical setup)
- ✅ Can view: Events, Results
- ✅ Can see **only events they're assigned to**
- ❌ Cannot create users
- ❌ Cannot assign users to events

#### Event Admin
- ✅ Can create: Tabulator, Judge, Auditor, Host
- ✅ Can manage: Events, Judges, Contestants, Results
- ✅ Can see **only events they're assigned to**
- ❌ Cannot create: Super Admin, Event Admin, Event Organizer, Event Technical Admin
- ❌ Cannot assign users to events

### Event Assignment Rules

**Who sees what events?**
- **Super Admin**: Sees ALL events (no filtering)
- **All other users**: See ONLY events they're assigned to

**Automatic Assignments:**
- ✅ Event creator is automatically assigned to the event
- ✅ Judges are automatically assigned when assigned as judges to an event

**Manual Assignments:**
- Super Admin and Event Organizer can manually assign users to events
- Go to: Events → [Select Event] → "Assign Users to Event"

**Why is this important?**
- Users can only manage events they can see
- This provides better security and organization
- Each event organizer can manage their own events independently

### Recommended Workflow

1. **Super Admin** creates:
   - Event Organizer (for overall coordination)
   - Event Technical Admin (for technical setup)

2. **Super Admin or Event Organizer** creates event:
   - Event is created
   - Creator is automatically assigned to the event

3. **Super Admin or Event Organizer** assigns staff to event:
   - Assign Event Organizer to the event (if different from creator)
   - Assign Event Technical Admin to the event
   - This allows them to see and manage the event

4. **Event Organizer** creates:
   - Judges (for scoring)
   - Tabulators (for score management)
   - Auditors (for verification)
   - Hosts (for display management)

5. **Event Organizer** assigns judges to event:
   - Judges are automatically assigned to see the event
   - No need for separate event assignment

6. **Event Technical Admin** sets up:
   - Criteria
   - Rounds
   - Weights

7. **Event Admin** (if needed) can also:
   - Create judges and other staff
   - Manage event operations
   - (Must be assigned to event first to see it)

---

## 🔄 Flexible Pageant Structure

This structure can be adapted for:
- **Beauty Pageants**: Add more rounds (Photogenic, Best in Swimsuit, etc.)
- **Talent Competitions**: Focus on talent rounds
- **Scholarship Pageants**: Add interview rounds
- **Cultural Pageants**: Add cultural presentation rounds

Simply:
1. Add more levels/rounds as needed
2. Create criteria specific to your pageant
3. Adjust weights based on importance
4. Add more contestants/judges

---

## 📞 Support

If you encounter issues:
1. Check the error message
2. Verify all required fields are filled
3. Ensure judges are properly assigned
4. Check that criteria weights total 100%
5. Review the JUDGE_SETUP_GUIDE.md for judge assignment issues
6. **"I can't see my event"**: Make sure you're assigned to the event (Super Admin sees all events)
7. **"Access denied to event"**: You need to be assigned to the event by Super Admin or Event Organizer

---

**Happy Testing! 🎉**

