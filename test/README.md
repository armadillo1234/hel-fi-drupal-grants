# Robot tests

## Running with make / docker

Run command `make test-robot`

This creates and runs a docker container with python and robot framework installed. The whole test suite is ran and logs will be in `test/logs` folder.

To run only specific tests, run `ROBOT_OPTIONS="-t Test_name_here" make test-robot
`, where `Test_name_here` is the name of the test case in robot file, spaces replaced with underscores.

## Running in local environment

### Requirements

- python3
- pip
- chromedriver

### Install

- `cd test`
- `python3 -m venv env`
- `source env/bin/activate`
- `pip install -r requirements.txt`
- `rfbrowser init`

### Running

- `cd test`
- `source env/bin/activate`
- `ROBOT_OPTIONS="--variable environment:local --variable browser:chrome" robot -d logs/ tests/*.robot`
