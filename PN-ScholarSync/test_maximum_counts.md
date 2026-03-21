# Penalty Escalation System Implementation

## Overview
The system implements a penalty escalation system where each severity level has a maximum count. Once a student exceeds the maximum count for a severity, they escalate to the next penalty level.

## Penalty Escalation by Severity

| Severity   | Maximum Count | Base Penalty (1st-Max)      | Escalated Penalty (Max+1)   | Logic                           |
|------------|---------------|-----------------------------|-----------------------------|--------------------------------|
| Low        | 3 offenses    | Verbal Warning              | Written Warning             | 1st-3rd = VW, 4th+ = WW       |
| Medium     | 2 offenses    | Written Warning             | Probationary of Contract    | 1st-2nd = WW, 3rd+ = Pro      |
| High       | 2 offenses    | Probationary of Contract    | Termination of Contract     | 1st-2nd = Pro, 3rd+ = Exp     |
| Very High  | 1 offense     | Termination of Contract     | Termination of Contract     | 1st+ = Exp (no escalation)    |

## How It Works

### Backend Implementation
1. **Escalation Logic**: Each severity has a base penalty and an escalated penalty
2. **Offense Counting**: System counts existing violations within the same severity
3. **Penalty Assignment**: Base penalty for offenses ≤ max count, escalated penalty for offenses > max count
4. **API Response**: Returns offense count, maximum count, escalation status, and both penalty levels

### Frontend Implementation
1. **Dynamic Display**: Shows current offense number with severity and maximum
2. **Escalation Indicator**: Displays "(escalated - exceeds max: X)" when escalated
3. **Real-time Updates**: Updates as student and violation type are selected

## Example Scenarios

### Low Severity Violations (Max: 3, Base: VW, Escalated: WW)
- 1st offense: Verbal Warning
- 2nd offense: Verbal Warning
- 3rd offense: Verbal Warning
- **4th offense: Written Warning (escalated)**
- 5th+ offense: Written Warning (escalated)

### Medium Severity Violations (Max: 2, Base: WW, Escalated: Pro)
- 1st offense: Written Warning
- 2nd offense: Written Warning
- **3rd offense: Probationary of Contract (escalated)**
- 4th+ offense: Probationary of Contract (escalated)

### High Severity Violations (Max: 2, Base: Pro, Escalated: Exp)
- 1st offense: Probationary of Contract
- 2nd offense: Probationary of Contract
- **3rd offense: Termination of Contract (escalated)**
- 4th+ offense: Termination of Contract (escalated)

### Very High Severity Violations (Max: 1, Base: Exp, Escalated: Exp)
- 1st offense: Termination of Contract
- 2nd+ offense: Termination of Contract (no escalation possible)

## Display Format
- Within Maximum: "2nd offense in Medium severity (max: 2)"
- Escalated: "4th offense in Low severity (escalated - exceeds max: 3)"

## Benefits
1. **Progressive Discipline**: Allows escalation when students repeatedly violate same severity
2. **Deterrent Effect**: Students know consequences increase with repeated violations
3. **Transparency**: Clear visibility of escalation thresholds and current status
4. **Fairness**: Consistent escalation rules across all severity levels
5. **Flexibility**: Different escalation paths for different severity levels
